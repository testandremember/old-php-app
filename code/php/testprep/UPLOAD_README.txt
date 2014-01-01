IMPORTANT:
Make sure that all .htaccess files are uploaded.
There is a .htaccess file in /site/inc
There is a .htaccess file in /site/backups
Create a cron job to run every night which executes:
php /site/cron/backup-database-cron.php