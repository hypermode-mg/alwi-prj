import logging
import os
import time
from threading import Thread
import requests
import telebot

from dotenv import load_dotenv

load_dotenv()

#TELEGRAM_CHAT_ID = os.getenv("TELEGRAM_CHAT_ID")


#config bot
TELEGRAM_TOKEN = os.getenv("TELEGRAM_TOKEN")

apikey = "dme-q1we-lele"
http_server_aw="http://localhost/hle2" #server aw
#config bot end



bot=telebot.TeleBot(TELEGRAM_TOKEN, parse_mode='html')

#функиця регистрации пользователя. Спросим email. и если он есть, то добавим chat_id в базу
def register_user(chat_id,useremail):

   
    params = {
        "chat_id": chat_id,
        "useremail": useremail,
    }

    try:
        resp = requests.get(http_server_aw+'/api/tg_register.php', params=params)
        if resp.status_code==200:
            resp_data=resp.json()

            if resp_data['status']=='ok':
                bot.send_message(chat_id, 'Регистрация прошла успешно! Сообщения будут приходить в этот чат')
            else:
                bot.send_message(chat_id, 'Произошла ошибка, сообщите администратору')
            
        else:
            bot.send_message(chat_id, 'Что-то пошло не так, сообщите администратору')
      
    except requests.exceptions.Timeout:
        raise Exception(error_messages["Timeout"])

    except requests.exceptions.RequestException:
        raise Exception(error_messages["RequestException"])


#основная функция бота с где он слушает. Если пишем всякое - сообщаем, что регистрироваться через reg
@bot.message_handler(content_types=['text'])
def start(message):
    if message.text == '/reg':
        bot.send_message(message.from_user.id, 'Попробую зарегистрировать. Собщите свой e-mail')
        
        bot.register_next_step_handler(message, get_useremail) #следующий шаг – функция get_useremail
    else:
        bot.send_message(message.from_user.id, 'Напиши /reg')


#получим useremail
def get_useremail(message):
    global name
    name = message.text

    #пробуем зарегистрировать пользователя
    register_user(message.from_user.id,name)




def get_messages():
    params = {
        "authkey": apikey,
    }

    messages = requests.get(http_server_aw+'/api/tg_sendman.php', params)

    return messages.json()

#uncomment this line to enable register
#Thread(target=bot.infinity_polling, args=()).start()

#основной цикл
while True:
    new_messages = get_messages()

#    print(new_messages)

    for msg in new_messages['messages']:
   
        mess_split = msg['message'].split(';')
        if(mess_split[0].find('ON-LINE')!=-1):
            message_to_bot = '<b>'+mess_split[0]+'</b>👍\n'+mess_split[1]
        else:
            message_to_bot = '<b>'+mess_split[0]+'</b>😡\n'+mess_split[1]
    #    print(msg['chat_id'])
        bot.send_message(msg['chat_id'], message_to_bot, parse_mode='HTML')
           
    time.sleep(60)
 