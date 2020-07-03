# Say 99

Simple drop-in plugin for WordPress to log on file the memory usage in WP.

The plugin needs to be copied as single file in the wp-content/mu-plugins folder. It doesn't
need to be activated.

It creates 4 log files inside the wp-content/logs/say-99 folder: admin.txt, cron.txt,
main.txt, ajax.txt.

On those file you can find some information about memory usage.

It's just a piece of code, every contribution would be great!

By default is logs only the admin and cron context. Change the definitions on the top
of the file or add your definitions on wp-config.php to change the default behavior.