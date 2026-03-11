# -*- coding: utf-8 -*-
import logging
import os
import time
import requests
import json
import datetime


logging.basicConfig(
    level=logging.DEBUG,
    filename="main.log",
    filemode="a",
    format="%(asctime)s, %(levelname)s, %(name)s, %(message)s",
)

#config sensor
#путь до сервера AlertsOnWings например http://192.168.1.9/hle2
http_server_aw="http://localhost/hle2"

#IP host where sensor is installed
host_sensor="192.168.1.15"

#api key to access API
apikey = "dme-q1we-lele" 

#config sensor end

sensor_version = "1.0"


#функция получения конфига
def get_config_sensor(host):

   
    params = {
        "host": host,
        "sensor_version": sensor_version,
        "authkey": apikey,
    }

    try:
#        resp = requests.get(http_server_aw+'/api/sensordata_grab.php', params=params)
        resp = requests.get(http_server_aw+'/api/sensordata_config.php', params=params)

        if resp.status_code==200:
            resp_data=resp.json()
            logging.debug("Данные получены")
            
            with open('sensorconfig.json', 'w', encoding='utf-8') as f:
                json.dump(resp_data, f, ensure_ascii=False, indent=4)
        else:
            logging.debug("Ошибка получения данных")

           
      
    except Exception as e:
        logging.debug(f"Возникла ошибка {e}")


def update_sensor_data():

    # Opening JSON file
    f = open('sensorconfig.json')
 
# returns JSON object as 
# a dictionary
    sensors = json.load(f)
 
    
# Iterating through the json
# list
    for sensor in sensors['sensors']:
        #если сенсор не опрашивался ни разу, зададим ему текущую дату
        if(sensor['sensor_lastpolldate']=="0"):
            sensor['sensor_lastpolldate']=int(time.time())

        if(sensor['sensor_pollperiod']!=""):
            #если настало время выполнить опрос
            if((int(sensor['sensor_lastpolldate'])+int(sensor['sensor_pollperiod'])*60)<int(time.time())):
                get_sensor_data(sensor)

        if(sensor['sensor_polltime']!=""):
            #если настало время выполнить опрос
            now = datetime.datetime.now()
           

            if(sensor['sensor_polltime'] == f"{now.hour} {now.minute}" ):
                get_sensor_data(sensor)


    with open('sensorconfig.json', 'w', encoding='utf-8') as f:
        json.dump(sensors, f, ensure_ascii=False, indent=4) 
    

# Closing file
    f.close()

#обработка сенсоров
def get_sensor_data(sensor):

    #существование каталога
    if(sensor['sensor_type']=="linux_dirExist"):
    #спецслово [:curdate]

       path_data = sensor['sensor_cmddata']
       if(sensor['sensor_cmddata'].find(':curdate')> -1):
           path_data = sensor['sensor_cmddata'].replace(':curdate',datetime.datetime.now().strftime('%Y%m%d'))

       if(sensor['sensor_cmddata'].find(':prevdate') > -1):
           path_data = sensor['sensor_cmddata'].replace(':prevdate',(datetime.datetime.now() - datetime.timedelta(1)).strftime('%Y%m%d'))
       

       if(os.path.exists(path_data)):
           sensor['sensor_value']=1
       else:
           sensor['sensor_value']=0
       sensor['sensor_lastpolldate']=int(time.time())
       sensor_filename=str(int(time.time()))+"_"+sensor['sensor_id']+"_sensor.json"

       with open("sensordata/"+sensor_filename, 'w', encoding='utf-8') as f:
           json.dump(sensor, f, ensure_ascii=False, indent=4) 

    #размер каталога
    if(sensor['sensor_type']=="linux_dirSize"):

       path_data = sensor['sensor_cmddata']
    #спец слова в пути
       if(sensor['sensor_cmddata'].find(':curdate')> -1):
           path_data = sensor['sensor_cmddata'].replace(':curdate',datetime.datetime.now().strftime('%Y%m%d'))

       if(sensor['sensor_cmddata'].find(':prevdate') > -1):
           path_data = sensor['sensor_cmddata'].replace(':prevdate',(datetime.datetime.now() - datetime.timedelta(1)).strftime('%Y%m%d'))

       if(os.path.exists(path_data)):
           sensor['sensor_value']=int(get_dir_size(path_data)/1024/1024)
       else:
           sensor['sensor_value']=0

       sensor['sensor_lastpolldate']=int(time.time())
       sensor_filename=str(int(time.time()))+"_"+sensor['sensor_id']+"_sensor.json"

       with open("sensordata/"+sensor_filename, 'w', encoding='utf-8') as f:
           json.dump(sensor, f, ensure_ascii=False, indent=4) 

#обработка сенсоров
def send_sensor_data():
    
    for filename in os.listdir("sensordata"):
       with open(os.path.join(os.getcwd(), "sensordata/"+filename), 'r') as f:
           sensor = json.load(f)
           
           params = {
               "sensor_id": sensor['sensor_id'],
               "sensor_value": sensor['sensor_value'],
               "authkey": apikey,
           }

           try:
       
               resp = requests.get(http_server_aw+'/api/sensordata_fetch.php', params=params)
               #resp = 0

               if resp.status_code==200:
                   #resp_data=resp.json()
                   logging.debug("Данные отправлены по сенсору "+sensor['sensor_id'])
                   f.close()
                   os.remove("sensordata/"+filename)
                   
               else:
                   logging.debug("Ошибка отправки данных по сенсору "+sensor['sensor_id'])
                   f.close()
                   break

           except Exception as e:
               logging.debug(f"Возникла ошибка передачи данных {e} по сенсору "+sensor['sensor_id'])



def get_dir_size(path):
    # Проходим по директории и суммируем размеры файлов
    return sum(os.path.getsize(os.path.join(dp, f)) for dp, dn, fn in os.walk(path) for f in fn if os.path.isfile(os.path.join(dp, f)))


logging.debug("Начало работы.")

#удалим файл конфигурации на старте.
try:
    os.remove("sensorconfig.json")
except OSError:
    pass

get_config_sensor(host_sensor)
#основной цикл

while True:

    update_sensor_data()
    send_sensor_data()
           
    time.sleep(60)
 