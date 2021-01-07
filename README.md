<!-- PROJECT LOGO -->
![Imgur](https://i.imgur.com/Ll9A5BP.png)
![PHP-Size](https://img.shields.io/github/languages/code-size/suxsx/trawo)
![php-files](https://img.shields.io/github/directory-file-count/suxsx/trawo)
![Stars](https://img.shields.io/packagist/stars/suxsx/trawo)
![PHP - License](https://img.shields.io/github/license/suxsx/trawo)


# TRAWO - Tracking Wolf
Easy to use program for tracking emails, documents, images and other objects that can make use of one <a href="https://en.ryte.com/wiki/Tracking_Pixel">pixel tracking</a>. The core of the program is based on the most straightforward way of tracking. You generate an image of one pixel, and by achieving that you also collect the time, date, IP and other useful information about the target. 

The project comes with a unique GUI that you can use with any core server you want.You can download it from google play: <a href="https://play.google.com/store/apps/details?id=com.knocon.trawo"> TRAWO Android APP </a> to be clear. You need a core server for the app to work. If you don't possess one, the app will be unable to track anything. 

The project does not come with any core servers for you to test on, you need to setup this for your self. This is because we don't want to sit on any sort of database that contains personal information. The app or the core does not relay any kind of information any further than to your own server. 

When this is stated, we do offer a brief guide to get started with your own server for free. If you are prepared to struggle and try it out, follow the rest of the guide under.


<!-- CONTENT -->
## How to install
***1. Download GUI versjon from google play:***
<br />
<a href='https://play.google.com/store/apps/details?id=com.knocon.trawo&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1'><img alt='Get it on Google Play' src='https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png' height="100"/></a>


***2. Dowload CORE folder from github***
```
core
│   an.png
│   api.php
│   def.php
│   func.php
│   index.php
│   setup.php
│
└───images
    |   index.php
    │   blank.png
    │   TRAWOBG.png
```

## Getting Started
I will assume that you already have your own apache server with php and mysql. If not go down to **Setup Server**, follow one of the paths and come back here after.

### Phase 1 - Copy Files
Start with copying the core folder to your server. And remeber to **chmod** it so it can read and write files. Place it in your root folder of the server. When thats done visit: **YOUR-SERVER-URL/core/setup.php** (eks. https://mydomian.com/core/setup.php)

You can also visit the core folder, but you will then most likely be met with some errors and a message like this:

```cmd
YOU NEED TO RUN SETUP!
Please visit: SETUP PAGE
When finnish please delete setup.php
```

### Phase 2 - Setup db connection and user
![SETUP-PAGE](https://i.imgur.com/OyvY7ul.png)

The setup page is very easy to use. Start by filling inn information for the database, and click create database. This will configure your db_config.php file and connect the core to the database. 

Finally create some users. You can create as many users you would like. Each user get there own token that you use as a username and password in the GUI or when communication with the api endpoints. 

By default each user gets access value **1**, this means they gain full access to everything. Alter it to 2, if you require the user to only just see the trackers they produce.You need to change this manually by login into the MySQL database and change the value in the user table. 

If everything done correct, you should recive a token. If there is something wrong, you will see an error message informing you to repair something.

### Phase 3 - Connect GUI
![CONNECT-PAGE](https://i.imgur.com/qjakjtt.png)

You need having a stable internet connection to make the app work. If you launch the app and are missing, WIFI are you asked to correct the problem. 

When thats completed the TRAWO GUI check if you have entered a valid token and URL. If you are lacking one of them, ether the URL or TOKEN. You get a popup requesting you to fix it.

On the settings page enter the token you got from **Phase 2** or your friend. Along with a complete url to the core. You need entering both the domain name and folder name for it to work. (eks. http://my-domain.com/core-folder).

And thats it. You have completed the 3rd Phase. On to the next one. 

### Phase 4 - Create and send your first tracker
![CREATE-PAGE](https://i.imgur.com/WdxXvwE.png)

You are now ready to create your first tracker. If you try to enter the tracker list, and you have completed the setup, and you don't have any tracker... Great you will then get a popup telling you to create one. 

On the tracker page fill inn:
- Mail to the person to track
    - This works as a id for the person. Therefore, you require it even if you are tracking only documents.
- Subject
- And what kind of tracker you are creating.

When all this is done, you get the tracking-id below. And you also get a html-code. You can copy-paste the code into your email program or enter just the url into word as an image and you will be able to see when people open the word document or read the email.

You can also just click generate email, and all the information gets pasted into the mail app. 

>**NB! GMAIL is not supported** if you want to use **Generate email** we recommend that you use [outlook](https://play.google.com/store/apps/details?id=com.microsoft.office.outlook). 
>
>This is because there is a problem when you want to insert IMG links to Gmail. For the moment thats not supported. 

The magic happens when the link is visited. Each time someone or something lookup the link to obtain the image, they get logged. So the tracker works everywhere you can use a link to insert images. As long as the program downloads the image each time, it gets opened.  

### Phase 5 - View the information
![VIEW-PAGE](https://i.imgur.com/kUORR5j.png)
When you have, send the tracking mail. Sit back and relax. If you visit the tracker list, you can see that there instantly is a tracker present. 

Trackers communicate information about when it was generated, who it is to, the subject and of course a counter describing how many times it have been opened. 

If you click on it, you obtain a list with all the events regarding the tracker. The IP with a quick lookup, time and date and user agent that was used. 

If you at some point don't require the information anymore just swipe left and hit delete. This work also other places in the app. 

### Phase 6 - Explore
This is just the start of a extended journey. The GUI and CORE have more to offer. Try looking around creat contacts, polish them, try to track documents and much more. 

## Using TRAWO without GUI
If you dont want to use the GUI provied you can create your own, or use the API as you want. 

TRAWO have to endpoints **index.php** and **api.php**. API.php uses **application/json** post request, and index.php uses regular POST. All communication from the end point will be in JSON. 

### Using **index.php**
**To create a tracker from POST**:
```python
{URL-ADDR}\core\?token={YOUR-TOKEN}&subject={SUBJECT}&=addr={TO-EMAIL}
```
This will give following out put:
```python
{"tracker":"{TRACKER-ID}"}
```

**Log vivews of tracker with POST**:
```python
{URL-ADDR}\core\?image={TRACKERID}
```
Output given her will be in the form of a 1x1 transparent pixel image.

### Using **api.php**
To start you need to standar values. A token and a task. The token tells the API that you have access and the task tells it what to do.

```php
$jsonPost = array(
    'task' => '{TASK-TO-PREFORM}',
    'token' => '{YOUR-TOKEN}',
);
```

**Following tasks are allowed:**
- uploadimage
- updateprofile
- getcontactlist
- getcontactdetail
- maillist
- log
- valid
- getuser
- setcontactdetail
- delcon
- del
- createtracker
- validapi
- delevent

#### **uploadimage**
Use:
```php
$jsonPost = array(
    'task' => 'uploadimage',
    'token' => '{YOUR-TOKEN}',
    'con_id' => '{CONTACT-ID-THAT-GET-NEW-IMAGE}',
    'image_name' => '{IMAGE-NAME}',
    'image_data' => '{IMAGE-DATA-IN-BYTE}',
);
```
Returns action. Action is **true** if upload complete. If not contains the error message.
```php
{"action":"true"}
```

#### **updateprofile**
Use:
```php
$jsonPost = array(
    'task' => 'updateprofil',
    'token' => '{YOUR-TOKEN}',
    'con_id' => '{CONTACT-ID-TOCHANGE}',
    'firstname' => '{FIRSTNAME}',
    'lastname' => '{LASTNAME}',
    'email' => '{EMAIL}',
    'phone' => '{PHONE}',
    'adress' => '{ADRESS}',
    'company' => '{COMPANY}',
);
```
Returns action. Action is **true** if upload complete. If not contains the error message.
```php
{"action":"true"}
```

#### **getcontactlist**
Use:
```php
$jsonPost = array(
    'task' => 'updateprofil',
    'token' => '{YOUR-TOKEN}',
);
```
Returns an array with all the contacts for that token user. Action is **true** if all good. complete. If not contains the error message.
```php
[{"action":"true","shortid":{ID},"email":{EMAIL},"firstname":"N\/A","lastname":"N\/A","phone":"N\/A","adr":"N\/A","company":"N\/A","del":"false","tracker_count":{HOW-MANY-TRACKERS},"image":"/images\/blank.png"}, ]
```

#### **getcontactdetail**
Use:
```php
$jsonPost = array(
    'task' => 'updateprofil',
    'token' => '{YOUR-TOKEN}',
    'con_id' => '{CONTACT-ID}',
);
```
Returns contact detail for one contact, if you have access to that one contact. Action is **true** if all good. If not contains the error message.
```php
{"action":"true","conid":3,"email":"test@test.com","firstname":"Testname","lastname":"Lastname","phone":"123488","adress":"Test Adresse","company":"companytest","lastip":null,"trackercount":1,"trackerevent":0,"image":"https:\/\/localhost\/core\/images\/blank.png"}
```

#### **maillist**
Use:
```php
$jsonPost = array(
    'task' => 'maillist',
    'token' => '{YOUR-TOKEN}',
);
```
Returns a list of all trackers current token user have created.
```php
[{"id":{TRACKER-ID},"from":"3","subject":"test 1","dato":"2020-10-21 08:15:34","type":"email","to_mail":"test@test.com","to_firstname":"Testname","to_lastname":"Lastname","to_phone":"123488","to_adress":"Test Adresse","to_company":"companytest","to_image":"https:\/\/localhost\/core\/images\/blank.png","from_mail":"test@test.com","from_firstname":"Testname","from_lastname":"Lastname","from_phone":"123488","from_adress":"Test Adresse","from_company":"companytest","from_image":"https:\/\/localhost\/core\/images\/blank.png","open_count":"0"}]
```

#### **log**
Use:
```php
$jsonPost = array(
    'task' => 'log',
    'token' => '{YOUR-TOKEN}',
    'id' => '{TRACKER-ID}',
);
```
Returns a list of all events for a tracker. If there are no events the list is empty.
```php
[{"uid":{UID},"idlog":{TRACKER-ID},"ip":{IP},"date":{TIMESTAMP},"useragent":{USER-AGENT},"ip_country":{COUNTRY},"ip_region_name":{REGION},"ip_city":{CITY},"ip_timezone":{TIMEZONE},"ip_isp":{ISP},"ip_org":{ISP-ORG},"ip_as":{ISP-AS}},]
```

#### **valid**
Use:
```php
$jsonPost = array(
    'task' => 'valid',
    'token' => '{YOUR-TOKEN}',
    'id' => '{TRACKER-ID}',
    'det' => 'on/off'
);
```
Check if if a tracker is valid, if details are on it returns tracker information. If not true or false.
```php
//det = off
{"tracker_valid":"true"}

//det = on
{"tracker_id":{TRACKER-ID},"tracker_to":{EMAIL-TO-IN-TEXT},"tracker_subject":{SUBJECT-IN-TEXT}}
```

#### **getuser**
Use:
```php
$jsonPost = array(
    'task' => 'getuser',
    'token' => '{YOUR-TOKEN}',
);
```
Return user information. If user does not exist return action with error.
```php
{"userid":2,"username":"Tester","access":"Write","email":"test@test.com","firstname":"Testname","lastname":"Lastname","phone":"123488","adress":"Test Adresse","company":"companytest","conid":3,"lastip":"127.0.0.1","trackercount":1,"trackerevent":0,"image":"https:\/\/localhost\/core\/images\/blank.png"}
```

#### **setcontactdetail**
Use:
```php
$jsonPost = array(
    'task' => 'setcontactdetail',
    'token' => '{YOUR-TOKEN}',
    'con_id' => '{CONTACT-ID-TOCHANGE}',
    'firstname' => '{FIRSTNAME}',
    'lastname' => '{LASTNAME}',
    'email' => '{EMAIL}',
    'phone' => '{PHONE}',
    'adress' => '{ADRESS}',
    'company' => '{COMPANY}',
);
```
Set contact details for a contact of current user. Return True when complete if not true contains error message.
```php
{"action":"true"}
```

#### **delcon**
Use:
```php
$jsonPost = array(
    'task' => 'delcon',
    'token' => '{YOUR-TOKEN}',
    'con_id' => '{CONTACT-ID-TO-DELETE}',
);
```
Delete a contact. Return True when complete if not true contains error message.
```php
{"action":"true"}
```

#### **del**
Use:
```php
$jsonPost = array(
    'task' => 'del',
    'token' => '{YOUR-TOKEN}',
    'id' => '{TRACKER-ID-TO-DELETE}',
);
```
Delete a tracker will all events. Return True when complete if not true contains error message.
```php
{"action":"true"}
```

#### **createtracker**
Use:
```php
$jsonPost = array(
    'task' => 'createtracker',
    'token' => '{YOUR-TOKEN}',
    'addr' => '{TO-ADDR}',
    'subject' => '{SUBJECT}',
    'file' => '{FILE/EMAIL/DOC/PDF}',
);
```
Create a new tracker, returns tracker id if token exist.
```php
{"tracker":"{TRACKER-ID}"}
```

#### **delevent**
Use:
```php
$jsonPost = array(
    'task' => 'delevent',
    'token' => '{YOUR-TOKEN}',
    'uid' => '{uid-event}',
);
```
Delete current event if allowd by user, return true when done, if error returns error in action. 
```php
{"action":"true"}
```


## Simple test script
To check if its working, you can use this simpel test script in php. Just change the JSON array to your own commands.

```php
<?php

//JSON ARRAY
$jsonPost = array(
    'task' => '{TASK-TO-PREFORM}',
    'token' => '{YOUR-TOKEN}',
);

//Core URL
$url = 'localhost/core/api.php';

//Initiate cURL.
$ch = curl_init($url);

//Encode the array into JSON.
$jsonDataEncoded = json_encode($jsonPost);
 
//Tell cURL that we want to send a POST request.
curl_setopt($ch, CURLOPT_POST, 1);
 
//Attach our encoded JSON string to the POST fields.
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
 
//Set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
 
//Execute the request
$result = curl_exec($ch);

?>
```

## Setup Server
Brief guide to setup your own apache server with php and mysql. There are plenty of guides, but here are some to convince you to start. If you don't find any that you like, just google "How to setup Apache and MySQL" there are also plenty of free websites that provide if for you.

- Windows users:
    - [WAMP-Server](https://www.shaileshjha.com/how-to-install-wamp-server-on-windows-10/)
    - [XAMPP-Server](https://www.apachefriends.org/faq_windows.html)
- Mac OS users:
    - [Apache-Server](https://getgrav.org/blog/macos-bigsur-apache-multiple-php-versions)
    - [Apache-PHP-MySQL](https://www.journaldev.com/1456/how-to-install-apache-php-and-mysql-on-mac-os-x)
- Linux users:
    - [Ubuntu-Apache](https://ubuntu.com/tutorials/install-and-configure-apache#1-overview)
    - [LAMP-Server](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-ubuntu-18-04)
- Raspberry PI:
    - [Apache-PHP](https://www.raspberrypi.org/documentation/remote-access/web-server/apache.md)
    - [LAMP-Server](https://howtoraspberrypi.com/how-to-install-web-server-raspberry-pi-lamp/)

## Common Information
- Read the CODE of Conduct before you edit: [Code of Conduct](https://github.com/suxSx/trawo/blob/master/CODE_OF_CONDUCT.md)<br />
- We use MIT License: [MIT](https://github.com/suxsx/trawo/blob/master/LICENSE.md)
- For more projects and contact visit our [homepage](https://knoph.cc)