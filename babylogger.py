#! /usr/bin/python3
# IMPORT STATEMENTS
import RPi.GPIO as GPIO
import os
import sys
import time
import pymysql
import datetime

#----------------------------------------------------------
# CONFIGURATION SETTINGS
led_pin = 16
button_pins = {
    "breastfed": 2,
    "bottle": 3,
    "pee": 4,
    "poo": 17,
    "sleep": 27,
    "wake": 22,
    "bath": 10,
    "cry": 9
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

GPIO.setwarnings(False)
GPIO.setmode(GPIO.BCM)

GPIO.setup(led_pin, GPIO.OUT)
for pin in button_pins.values():
    GPIO.setup(pin, GPIO.IN, pull_up_down=GPIO.PUD_UP)

#----------------------------------------------------------

def blink_pattern(pattern):
    """Blink LED based on a predefined pattern."""
    p = GPIO.PWM(led_pin, 1000)
    p.start(50)
    for duration in pattern:
        time.sleep(duration)
        p.stop()
        time.sleep(0.1)
    p.stop()

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

#---------------------------------------------------------
try:
    time.sleep(1.0)
    # STATUS LED: blinks .-. = R in Morse Code ("Ready")
    blink_pattern([0.1, 0.3, 0.1])
    print("Baby Logger running...")

    while True:
        for event, pin in button_pins.items():
            if not GPIO.input(pin):  # Button pressed
                log_event(event)
                # LED Feedback
                blink_pattern([0.1, 0.1, 0.1, 0.3])
                time.sleep(0.5)  # Debounce delay

        # Check for shutdown (press first three buttons together)
        if (not GPIO.input(button_pins["breastfed"]) and
            not GPIO.input(button_pins["bottle"]) and
            not GPIO.input(button_pins["pee"])):
            print("Shutdown requested...")
            for _ in range(5):
                blink_pattern([1])
            GPIO.output(led_pin, GPIO.HIGH)
            os.system("sudo shutdown -h now")

        time.sleep(0.1)  # General debounce

except KeyboardInterrupt:
    print("Exiting program...")
finally:
    db.close()
    GPIO.cleanup()
    print("GPIO and database connection closed.")
