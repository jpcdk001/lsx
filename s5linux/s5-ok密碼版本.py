# -*- coding: gbk -*-
import socket
import struct
import threading
import time
import atexit

active_sockets = []
active_threads = []
sockets_lock = threading.Lock()
threads_lock = threading.Lock()


def get_local_ip():
    try:
        local_ip = socket.gethostbyname(socket.gethostname())
        return local_ip
    except Exception as e:
        print(f"Unable to get local IP: {e}")
        return None

def handle_client(client_socket):
    # Receive the SOCKS5 method selection message
    method_selection_msg = receive_all(client_socket, 2)
    if method_selection_msg is None:
        client_socket.close()
        return
    version, nmethods = struct.unpack('!BB', method_selection_msg)

    # Receive the list of methods
    methods = receive_all(client_socket, nmethods)
    if methods is None:
        client_socket.close()
        return

    # Check the methods and select a method (in this case, we select 'username/password authentication')
    if 2 not in methods:  # 2 is the code for username/password authentication
        # No acceptable methods
        response = struct.pack('!BB', 5, 0xFF)
        client_socket.sendall(response)
        client_socket.close()
        return
    else:
        # 'Username/password authentication' is acceptable
        response = struct.pack('!BB', 5, 2)  # 2 is the code for username/password authentication
        client_socket.sendall(response)

    # Receive the username/password
    auth_version = receive_all(client_socket, 1)
    if auth_version is None or auth_version[0] != 1:  # The auth version should be 1
        client_socket.close()
        return
    username_length = receive_all(client_socket, 1)
    if username_length is None:
        client_socket.close()
        return
    username = receive_all(client_socket, username_length[0])
    if username is None:
        client_socket.close()
        return
    password_length = receive_all(client_socket, 1)
    if password_length is None:
        client_socket.close()
        return
    password = receive_all(client_socket, password_length[0])
    if password is None:
        client_socket.close()
        return

    # Check the username and password
    if username.decode('utf-8') != 'ceshi' or password.decode('utf-8') != '123456abc...':
        # Invalid username or password
        response = struct.pack('!BB', 1, 0xFF)
        client_socket.sendall(response)
        client_socket.close()
        return
    else:
        # Valid username and password
        response = struct.pack('!BB', 1, 0)
        client_socket.sendall(response)

        header = receive_all(client_socket, 4)
        if header is None:
            client_socket.close()
            return

        version, command, _, address_type = struct.unpack('!BBBB', header)
        if command != 1:  # Only CONNECT is supported
            client_socket.close()
            return

        if address_type == 1:  # IPv4 address
            address = receive_all(client_socket, 4)
            if address is None:
                client_socket.close()
                return
            target_address = socket.inet_ntoa(address)
        elif address_type == 3:  # Domain name
            length = receive_all(client_socket, 1)
            if length is None:
                client_socket.close()
                return
            address = receive_all(client_socket, length[0])
            if address is None:
                client_socket.close()
                return
            target_address = address.decode('utf-8')
        else:  # Unsupported address type
            client_socket.close()
            return

        port = receive_all(client_socket, 2)
        if port is None:
            client_socket.close()
            return
        target_port = int.from_bytes(port, 'big')

        # Connect to the target server and send the response
        try:
            server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            server_socket.connect((target_address, target_port))

            # Send a successful response
            response = struct.pack('!BBBBIH', 5, 0, 0, 1, 0, 0)
            client_socket.sendall(response)
        except Exception:
            # Send a failed response
            response = struct.pack('!BBBBIH', 5, 1, 0, 1, 0, 0)
            client_socket.sendall(response)
            client_socket.close()
            return

        # ����ת��
        # Data forwarding
        def forward(source, destination):
            try:
                while True:
                    data = source.recv(4096)
                    if len(data) == 0:
                        break
                    while len(data) > 0:
                        sent = destination.send(data)
                        data = data[sent:]
            except socket.error:
                pass
            finally:
                source.close()
                destination.close()
                with sockets_lock:
                    if source in active_sockets:
                        active_sockets.remove(source)
                    if destination in active_sockets:
                        active_sockets.remove(destination)

        client_to_server = threading.Thread(target=forward, args=(client_socket, server_socket))
        server_to_client = threading.Thread(target=forward, args=(server_socket, client_socket))

        with sockets_lock:
            active_sockets.append(client_socket)
            active_sockets.append(server_socket)

        client_to_server.start()
        server_to_client.start()

        with threads_lock:
            active_threads.append(client_to_server)
            active_threads.append(server_to_client)

        # Wait for both threads to finish
        client_to_server.join()
        server_to_client.join()

        with threads_lock:
            if client_to_server in active_threads:
                active_threads.remove(client_to_server)
            if server_to_client in active_threads:
                active_threads.remove(server_to_client)

def forward_nonblocking(source, destination):
    source.setblocking(False)
    destination.setblocking(False)
    while True:
        try:
            data = source.recv(4096)
            if not data:
                break
            sent = destination.send(data)

            time.sleep(0.01)
        except Exception as e:
            if destination.fileno() == -1:  # The socket is closed
                print('Socket is already closed')
            else:
                raise e  # The exception is not because the socket is closed
    # ��Դ�Ͽ����߷������ݳ���ʱ���ر���������
    source.close()
    destination.close()

def receive_all(sock, length):
    data = b""
    while len(data) < length:
        packet = sock.recv(length - len(data))
        if not packet:
            return None
        data += packet
    return data

def start_proxy():
    # ���������׽���
    proxy_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    proxy_socket.bind(('0.0.0.0', 5209))  # ������������ӿڵ�5209�˿�
    proxy_socket.listen(10)
    print('����������������������˿�:', 5209)

    while True:
        # �ȴ��ͻ�������
        client_socket, client_addr = proxy_socket.accept()
        print('��������', client_addr, '������')

        # �����̴߳���ͻ�������
        client_thread = threading.Thread(target=handle_client, args=(client_socket,))
        client_thread.start()

def cleanup():
    for sock in active_sockets:
        try:
            sock.close()
        except Exception:
            pass

    for thread in active_threads:
        try:
            thread.join()
        except Exception:
            pass

atexit.register(cleanup)


if __name__ == '__main__':
    start_proxy()
