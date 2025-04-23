@echo off
title DIMS_Messaging SMS Processor
set PHP=C:\xampp\php\php.exe
set ARTISAN=C:\xampp\htdocs\dims-messaging\artisan

echo Clearing and caching Laravel config, views, and events...

%PHP% %ARTISAN% optimize:clear
%PHP% %ARTISAN% config:cache
%PHP% %ARTISAN% view:cache
%PHP% %ARTISAN% event:cache

echo Starting SMS processing loop...

%PHP% %ARTISAN% app:sms-five-second-loop
