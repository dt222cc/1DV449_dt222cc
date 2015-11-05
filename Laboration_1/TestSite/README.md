# weekend-booking-web-site
This is not a real web site its just for an assignment where students writes a web agent scraping three diffrent sites

## Install
Make sure you have the following installed on your system:
* Virtual Box [https://www.virtualbox.org/](https://www.virtualbox.org/)
* Vagrant [https://www.vagrantup.com/](https://www.vagrantup.com/)

Now, do:

1. Pull (`git clone https://github.com:thajo/weekend-booking-web-site.git`) into your directory. 

2. Start the virtual machine using `vagrant up` (May take 5 - 10 minutes this first time. Ignore red command line statements and warnings.)

3. SSH into the machine using  `vagrant ssh`

4. Change directory to `cd /vagrant`

5. Install depencies for the node application `npm install` 

6. start the application running `node app`

## Daily workflow
1. Start out by `vagrant up` your machine and ssh into it (`vagrant ssh`). Change directory to `cd /vagrant`.

2. start the application running `node app`

3. Open up a browser on your local machine and visit the url `http://localhost:8080`

4. Let your web agent consume that base-url and start writing your code.

5. When you are done simply `ctrl+c` in the vagrant-terminal to stop the server, `exit` to  exit the ssh-session and do a `vagrant halt` to stop the machine or `vagrant suspend` to only suspend it.

6. Don´t forget to do lots of commits and push your code to the repo shared with the user "1DV449"
