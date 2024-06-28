from flask import Flask, request, render_template, redirect, url_for
from flask_mysqldb import MySQL
import requests
import os
import time
from threading import Thread

app = Flask(__name__)

# Configuração do banco de dados
app.config['MYSQL_HOST'] = 'localhost'
app.config['MYSQL_USER'] = 'root'
app.config['MYSQL_PASSWORD'] = '@Delta477'
app.config['MYSQL_DB'] = 'delta_ti_network'

mysql = MySQL(app)

# Configuração do Telegram
TELEGRAM_BOT_TOKEN = 'SEU_TELEGRAM_BOT_TOKEN'
TELEGRAM_CHAT_ID = 'SEU_CHAT_ID'

@app.route('/')
def index():
    cursor = mysql.connection.cursor()
    cursor.execute("SELECT * FROM hosts")
    hosts = cursor.fetchall()
    cursor.close()
    return render_template('index.html', hosts=hosts)

@app.route('/add_host', methods=['POST'])
def add_host():
    host_name = request.form['host_name']
    ip_address = request.form['ip_address']
    message_down = request.form['message_down']
    message_up = request.form['message_up']

    cursor = mysql.connection.cursor()
    cursor.execute("INSERT INTO hosts (host_name, ip_address, message_down, message_up) VALUES (%s, %s, %s, %s)", 
                   (host_name, ip_address, message_down, message_up))
    mysql.connection.commit()
    cursor.close()

    return redirect(url_for('index'))

@app.route('/delete_host/<int:id>')
def delete_host(id):
    cursor = mysql.connection.cursor()
    cursor.execute("DELETE FROM hosts WHERE id = %s", (id,))
    mysql.connection.commit()
    cursor.close()

    return redirect(url_for('index'))

def send_telegram_message(message):
    url = f"https://api.telegram.org/bot{TELEGRAM_BOT_TOKEN}/sendMessage"
    payload = {
        'chat_id': TELEGRAM_CHAT_ID,
        'text': message
    }
    requests.post(url, data=payload)

def ping_hosts():
    with app.app_context():
        while True:
            cursor = mysql.connection.cursor()
            cursor.execute("SELECT * FROM hosts")
            hosts = cursor.fetchall()
            cursor.close()

            for host in hosts:
                host_id, host_name, ip_address, message_down, message_up = host
                response = os.system(f"ping -c 1 {ip_address}")

                cursor = mysql.connection.cursor()
                if response == 0:
                    cursor.execute("INSERT INTO ping_history (host_id, status) VALUES (%s, 'up')", (host_id,))
                    send_telegram_message(message_up)
                else:
                    cursor.execute("INSERT INTO ping_history (host_id, status) VALUES (%s, 'down')", (host_id,))
                    send_telegram_message(message_down)

                mysql.connection.commit()
                cursor.close()

            time.sleep(60)  # Ping a cada 60 segundos

if __name__ == "__main__":
    thread = Thread(target=ping_hosts)
    thread.start()
    app.run(debug=True)
