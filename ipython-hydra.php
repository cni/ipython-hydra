<?php 
require_once('Logging.php');
$log = new Logging();
 
# set path and name of log file (optional)
$log->lfile('/var/log/ipython/ipython_server.log');
$user = getenv('REMOTE_USER');

# Create the user, if they don't already exist.
# THis script must be run as root. Make sure apache has 
# sudo privledge to run this. E.g.,:
# 
exec("sudo /usr/local/bin/addldapuser ".$user, $out, $stat);
if($stat==0){
    $log->lwrite('Created new user '.$user.'.');
}else{
    $log->lwrite('User not created ('.implode(', ',$out).').');
}

unset($out);
# The log file is owned by apache, so we'll let ipynb-launch write to a temp log
# and then copy that into our log. This will also help our log be a little more coherent
# when multiple processes are writing to it. (Might also consider locking the log with flock)
$tmplog = '/tmp/ipython_'.$user.'_'.getmypid().'.log';
exec("sudo -n -u $user /usr/local/bin/ipynb-launch ".$tmplog, $out, $stat);
$log->lwrite(file_get_contents($tmplog));
# TODO: Check return status. If 0, then a new kernel was launched. If 1, then exiting kernel was used.
$port = trim(file_get_contents("/home/$user/.ipython/lock"));
# The password might not be what we requested (e.g., if an existing kernel was used).
$passwd = trim(file_get_contents("/home/$user/.ipython/pass"));
$url = 'https://ipython.stanford.edu:'.$port;

$log->lclose();
echo "<form action='".$url."/login?next=%2F' method='post' name='frm'>\n";
echo "<input type='hidden' name='password' value='".$passwd."'>";
echo "<input type='submit' value='Log in' id='login_submit'>\n";
echo "</form>\n";
echo "<script language=\"JavaScript\">\ndocument.frm.submit();\n</script>\n";
?>
