# Baby-logger
This project logs baby's bodily functions and displays them on a webpage.
Many pediatricians recommend tracking your baby's feeding patterns, wet and dirty diapers to help know if he/she are eating enough - at least for the first few weeks. This is valuable information if there is a problem early on. The doctor can use this information to help with a diagnosis. Some parents track diapers and feedings for the first year, some parents do not track anything. There are several low tech ways to track diapers and feedings (paper or whiteboard) and increasingly there are apps to help with this. 
For the techy/geek parents, there are projects like this! :sunglasses:

Pictures of my first build are posted on Imgur: 

[The project was forked from [tommygober's Babby-logger project](https://github.com/tommygober/Baby-logger).]

## Hardware

* 1 - [Pi Zero W](https://www.amazon.com/Raspberry-Pi-Zero-Wireless-model/dp/B06XFZC3BX/ref=as_li_ss_tl?keywords=Pi+Zero+W&qid=1568671481&sr=8-3&linkCode=ll1&tag=neoduxcom-20&linkId=57dd1953d211a431ff6ac29425d3023c&language=en_US)
* 1 - [8+Gb microSD card](https://www.amazon.com/Sandisk-Ultra-Micro-UHS-I-Adapter/dp/B073K14CVB/ref=as_li_ss_tl?ie=UTF8&linkCode=ll1&tag=neoduxcom-20&linkId=58785ae4e36c928c594fe4e413d5cd1a&language=en_US)
* 1 - [MicroUSB Power supply](https://www.amazon.com/Raspberry-Supply-Charger-Adapter-Switch/dp/B07V7T93MY/ref=as_li_ss_tl?ie=UTF8&linkCode=ll1&tag=neoduxcom-20&linkId=7634220d688133a6b0b4c4adc850e2d3&language=en_US)
* 3 - [30mm arcade pushbuttons](https://www.amazon.com/Easyget-Standard-Arcade-Button-Microswitch/dp/B07D9C18MS/ref=as_li_ss_tl?ie=UTF8&linkCode=ll1&tag=neoduxcom-20&linkId=0c961811b57f9e40f54a9b4897e63890&language=en_US)
* 6 - [Jumper wires](https://www.amazon.com/Multicolored-Breadboard-Dupont-Jumper-Wires/dp/B073X7P6N2/ref=as_li_ss_tl?ie=UTF8&linkCode=ll1&tag=neoduxcom-20&linkId=2737a16c6e03c507f43c9efb6f64579c&language=en_US)
* 1 - [LED, 5mm (T 1-3/4), red](https://www.amazon.com/100pcs-Ultra-Bright-Emitting-Diffused/dp/B01GE4WHK6/ref=as_li_ss_tl?ie=UTF8&linkCode=ll1&tag=neoduxcom-20&linkId=68c44e1f176e93f2aea4a006098af3eb&language=en_US)
* 1 - [1/8W carbon film 220-ohm resistor](https://www.amazon.com/Watt-Carbon-Film-Resistors-5-Pack/dp/B007Z7MPRM/ref=as_li_ss_tl?ie=UTF8&linkCode=ll1&tag=neoduxcom-20&linkId=ed181e89698ee3188719301a8d94f075&language=en_US) (I even saw a deal for 100 LEDs *and* resistors on Amazon - so look for [something like this](https://www.amazon.com/EDGELEC-Diffused-Resistors-Included-Emitting/dp/B077X95F7C/ref=as_li_ss_tl?ie=UTF8&linkCode=ll1&tag=neoduxcom-20&linkId=4237ce09b0ba65da9d2774ff98de0a88&language=en_US).)

There's nothing special about any of the hardware listed. Get whatever is cheapest. If you have a 300-ohm resistor and a 3mm LED, that's fine; use those. If you already have a full size Pi, use it. In fact, I already had the Pi Zero W on hand, they're $5 at Microcenter if you live near one.
I used a Pi Zero W, again most any model would do - you will want wifi though. For making connections even easier (and later reuse of the Pi - so I don't have to solder wires in place), I found these [40-pin sockets](https://www.amazon.com/gp/product/B07D48WZTR/ref=as_li_ss_tl?ie=UTF8&linkCode=ll1&tag=neoduxcom-20&linkId=771e0a06d001ef4879ca458e0662131f&language=en_US) for the Pi. (If you're new to soldering, be careful not to bridge any two pins together!)
For input, I used three [30mm arcade-style pushbuttons](https://www.amazon.com/Easyget-Standard-Arcade-Button-Microswitch/dp/B07D9C18MS/ref=as_li_ss_tl?ie=UTF8&linkCode=ll1&tag=neoduxcom-20&linkId=0c961811b57f9e40f54a9b4897e63890&language=en_US) for ease of use. (Big buttons let the user see and press the button rather than small ones - small ones work fine for testing.) 

I connected the buttons to pins 13, 19, 26, and a common ground using jumper wires. You may choose [any GPIO pins you want](https://i.stack.imgur.com/yHddo.png), but be sure to note which ones you used and adjust the numbers in the Python script. You might notice the pins I use are all clustered near one another. I did not use Pin 21 and the GND pine by it because I'm reserving those for the [Adafruit Read-Only Pi](https://learn.adafruit.com/read-only-raspberry-pi/) write-pin jumper

I configured my pi to run headless since it does not require any rich, graphical user feedback. I installed a red 5mm LED on pin 16 with a 220-ohm resistor inline to act as a status light. It gives the user feedback as to which event was logged and could be setup to alert the user if there was an error. I did all my setup through ssh over a wireless connection. There are plenty of online tutorials for how to accomplish this by putting files in the /boot directory on the SD card.

## Updating OS and installing necessary packages

Run
```
sudo apt-get update
sudo apt-get upgrade -y
```

That might take some time.
Install Python 3's PIP tool for installing Python libraries.
```
sudo apt-get install python3-pip
```
Using PIP, install the Python 3 pymysql library.
```
pip install pymysql
```
###Update Pi to your current time zone
Open the raspi-config Tool: Run the following command in the terminal:
```
sudo raspi-config
```
Navigate to "Localisation Options":

    In the raspi-config menu, use the arrow keys to navigate.
    Select 5 Localisation Options (or a similar option depending on your version of raspi-config).
    Press Enter.

Select "Change Timezone":

    From the Localisation Options menu, select Change Timezone.
    Press Enter.

Choose Your Geographic Area:

    A list of geographic areas (e.g., Europe, America, Asia) will appear.
    Use the arrow keys to select your region (e.g., America).
    Press Enter.

Choose Your Time Zone:

    After selecting the geographic area, you'll see a list of time zones within that area.
    Select the appropriate time zone (e.g., New_York for EST in America).
    Press Enter.

Finish and Exit:

    Once you've set the time zone, you'll be returned to the raspi-config menu.
    Select Finish and press Enter.

Verify the correct time zone:
```
timedatectl
```



## Install MariaDB Server

Update your system:
```
sudo apt update && sudo apt upgrade -y
```

Install MariaDB
```
sudo apt install mariadb-server -y
```

Start the MariaDB service:
```
sudo systemctl start mariadb
```

Enable MariaDB to start on boot:
```
sudo systemctl enable mariadb
```

Check the status to confirm it’s running:
```
sudo systemctl status mariadb
```

Run the security script to set a root password and improve security.
When prompted:
    Set a strong root password.
    Remove anonymous users.
    Disallow root login remotely (recommended for security).
    Remove the test database.
    Reload privilege tables.
```
sudo mysql_secure_installation
```

Log in to the MariaDB shell as the root user, use the root password that you just set.
```
sudo mysql -u root -p
```

To verify that MariaDB is working:
```
SHOW DATABASES;
```

### Create a Database and User

Create a new database:
```
CREATE DATABASE babylogger;
```

Create a new user and grant permissions: Replace logger and password with your desired username and password:
```
CREATE USER 'logger'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON babylogger.* TO 'logger'@'localhost';
FLUSH PRIVILEGES;
```






## Install Apache Web Server and PHP

Update your system:
```
sudo apt update && sudo apt upgrade -y
```

Install Apache:
```
sudo apt install apache2 -y
```

Restart Apache to load PHP:
```
sudo systemctl restart apache2
```
Go to your Pi's IP address in a browser on a device connected to the same network as your Pi. You should see the default Apache page if everything is working correctly. 
If you need to find the IP address for the Pi:
```
hostname -I
```

You will want to delete the default index.html file that is generated by Apache setup.
```
sudo rm /var/www/html/index.html
```

Apache's default document root is /var/www/html. You need to place your index.php file in this directory.
```
sudo nano /var/www/html/index.php
```
Paste the contents there and edit the appropriate fields:
* db_host
* db_user
* db_passwd
* database
* GPIO pins
Ctrl-x and then Y to save the file (in nano)/

Set permissions: Ensure the file is readable by the web server:
```
sudo chmod 644 /var/www/html/index.php
```














## Python Script

Now copy the python script to your Pi
```
cd ~
mkdir Logger
cd Logger
```
Use nano or your editor of choice to create the script.
```
nano babylogger.py
```

Paste in the contents of the python script and CTRL-X then Y to exit and save. Don't forget to change GPIO numbers, database name, and password to match what you created. At this point you should also change the button names so that they correspond to the labels whatever you decide those to be.
You can try running the script with
```
sudo python3 babylogger.py
```
Push any of your buttons, if you don't get any error messages - CTRL-C to end the script.

Then open up your MySQL database again and check if anything has been written to the database.
Hopefully it will now show you the date, time, and which button has been pressed. Now you can close the MySQL interface and move on to setting up the webpage.


### Running as Service on boot

Make sure that the script is executable
```
chmod +x /home/pi/Logger/babylogger.py
```
Test the script to confirm that it works
```
/home/pi/Logger/babylogger.py
```

If you want to have the script run automatically whenever your Pi starts up, you can create a Systemd service file.
Create an empty file with nano or your editor of choice:
```
sudo nano /etc/systemd/system/babylogger.service
```
and then paste in the following (changing the username (default is pi) and the path if you've changed any of those)
```
[Unit]
Description=Baby Logger Service
After=network.target

[Service]
ExecStart=/usr/bin/python3 /home/pi/Logger/babylogger.py
Restart=always
User=pi
WorkingDirectory=/home/pi/Logger/
Environment=PYTHONUNBUFFERED=1

[Install]
WantedBy=multi-user.target
```
Ctrl-X and then Y to save the file.

Run the following command to reload the systemd manager configuration:
```
sudo systemctl daemon-reload
```
Enable the service so it starts automatically when the system boots:
```
sudo systemctl enable babylogger.service
```
Start the service manually to test it:
```
sudo systemctl start babylogger.service
```
To check on the status of your service:
```
sudo systemctl status babylogger.service
```
You should see something like:
```
● babylogger.service - Baby Logger Service
   Loaded: loaded (/etc/systemd/system/babylogger.service; enabled; vendor preset: enabled)
   Active: active (running) since Tue 2024-01-01 12:34:56 UTC; 5s ago
 Main PID: 1234 (python3)
    Tasks: 1 (limit: 4915)
   Memory: 10.5M
   CGroup: /system.slice/babylogger.service
           └─1234 /usr/bin/python3 /path/to/babylogger.py
```
This should have you up and running. Reboot and your system should come back up with your ```babylogger``` service running
```
sudo reboot now
```
