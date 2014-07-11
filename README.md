ipython-hydra
=============

Set of scripts to automatically launch ipython notebooks for each user who hits the page.

To set up ipython.stanford.edu, we did a standard linux-apache web server install and installed the latest ipython (with easy install). We also installed some other python packages that we wanted, like the nipy suite.

Additional configuration:

    sudo su
    addgroup --system ipython
    echo “www-data ALL=(%ipython) NOPASSWD: /usr/local/bin/ipynb-launch” >> /etc/sudoers.d/ipython
    echo “www-data ALL=(root) NOPASSWD: /usr/local/bin/addldapuser” >> /etc/sudoers.d/ipython
    chmod 0440 /etc/sudoers.d/ipython
    mkdir /var/log/ipython
    chown www-data.ipython /var/log/ipython
    chmod g+w /var/log/ipython

The php script calls the two bash scripts, where all the hard work happens:
 * addldapuser will add a new user to the system, if they don't already exist. It must be run as root. The password is disabled because we use Stanford's kerberos authentication, so if users log in via ssh they can authenticate with their SUNet ID and password. The new user is also added to the ipython group. If the user exists in Stanford LDAP, then their UID will be assigned by the LDAP result. Otherwise, a local UID is used. 
 
 * ipynb-launch will configure a new user's home directory (if it hasn't already been done) by setting up the ipython config file and checking out the tutorial notebooks from github. If the notebooks already exist, git pull is run to get any new items. However, we ensure that any existing notebooks are kept as the user last left them to ensure that we don't corrupt any of their edits. (NOTE: this part of the code sucks; I'm sure there is a more elgant way to do this, perhaps with some git-magic.) Finally, a new ipython notebook is launched, if needed. The port that it listens on and the auto-generated password are stored in files within the user's home directory (in ~/.ipython/lock and ~/.ipython/pass). If a server is already running for that user, and it seems to be listening on the correct port, then no action is taken.

After these scripts are called, the port and password are read from the user's home directory and a little intermediate page is returned to the client. This page just contains a little javascript to do a POST to the ipython notebook server login page with the auto-generated password. If all works as planned, the user will never see this hidden page nor the ipython login page. They should be taken directly to their active notebook server. Unfortunately, sometimes we've noticed that the auto-login fails, and the user is confronted with the ipython log in page requesting a password that they don't know. Reloading the ipython.stanford.edu page to let it redo the auto-login usually fixes it. (The user will also see the ipython login page if they click the logout button in the notebook. Is there any way to hack around this?)

To run the latest (development) branch of ipython, get it from github and then run "sudo pip install -e ." from the ipython directory. This installs the necessary packages and symlinks IPython into your system (in /usr/local/...) so that you can run the latest code.

