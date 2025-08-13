#!/usr/bin/python3
import os
import sys
import time
import pymysql
import datetime
import keyboard  

#----------------------------------------------------------
# CONFIGURATION SETTINGS
key_map = {
    "breastfed": "1",
    "bottle": "2",
    "pee": "3",
    "poo": "4",
    "sleep": "5",
    "wake": "6",
    "bath": "7",
    "cry": "8"
}

db_host = "localhost"
db_user = "logger"
db_pass = "password"
db_name = "babylogger"

#----------------------------------------------------------
# SETUP
try:
    db = pymysql.connect(host=db_host, user=db_user, password=db_pass, database=db_name)
    curs = db.cursor()
    print("Database connection successful.")
except pymysql.MySQLError as e:
    print(f"Database connection failed: {e}")
    sys.exit(1)

def log_event(event_type):
    """Log an event to the database."""
    curr_date = datetime.datetime.now().strftime("%Y-%m-%d")
    curr_time = datetime.datetime.now().strftime("%H:%M:%S")
    try:
        curs.execute(
            "INSERT INTO buttondata (tdate, ttime, type) VALUES (%s, %s, %s)",
            (curr_date, curr_time, event_type)
        )
        db.commit()
        print(f"{curr_date} {curr_time} - Event logged: {event_type.capitalize()}")
    except pymysql.MySQLError as e:
        print(f"Database error while logging {event_type}: {e}")
        db.rollback()

#----------------------------------------------------------
print("Baby Logger running...")
print("Press Numpad keys 1–8 to log events. Press ESC to exit.")

try:
    while True:
        for event, key in key_map.items():
            if keyboard.is_pressed(key):
                log_event(event)
                time.sleep(0.5)  # Debounce delay

        if keyboard.is_pressed("esc"):
            print("Exiting program...")
            break

        time.sleep(0.1)

except KeyboardInterrupt:
    print("Interrupted by user.")

finally:
    db.close()
    print("Database connection closed.")
