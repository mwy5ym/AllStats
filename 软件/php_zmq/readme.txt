根据php版本选择对应版本的libzmq.dll和php_zmq.dll

php-7.2.x-Win32-VC15-x64.zip -> x64\libzmq.dll + x64\7.2\TS\php_zmq.dll
php-7.2.x-Win32-VC15-x86.zip -> x86\libzmq.dll + x86\7.2\TS\php_zmq.dll
php-7.3.x-Win32-VC15-x64.zip -> x64\libzmq.dll + x64\7.3\TS\php_zmq.dll
php-7.3.x-Win32-VC15-x86.zip -> x86\libzmq.dll + x86\7.3\TS\php_zmq.dll
php-7.4.x-Win32-vc15-x64.zip -> x64\libzmq.dll + x64\7.4\TS\php_zmq.dll
php-7.4.x-Win32-vc15-x86.zip -> x86\libzmq.dll + x86\7.4\TS\php_zmq.dll

php-7.2.x-nts-Win32-VC15-x64.zip -> x64\libzmq.dll + x64\7.2\NTS\php_zmq.dll
php-7.2.x-nts-Win32-VC15-x86.zip -> x86\libzmq.dll + x86\7.2\NTS\php_zmq.dll
php-7.3.x-nts-Win32-VC15-x64.zip -> x64\libzmq.dll + x64\7.3\NTS\php_zmq.dll
php-7.3.x-nts-Win32-VC15-x86.zip -> x86\libzmq.dll + x86\7.3\NTS\php_zmq.dll
php-7.4.x-nts-Win32-vc15-x64.zip -> x64\libzmq.dll + x64\7.4\NTS\php_zmq.dll
php-7.4.x-nts-Win32-vc15-x86.zip -> x86\libzmq.dll + x86\7.4\NTS\php_zmq.dll

如果忘记当时是哪个安装包了就检查下phpinfo()

Architecture x64 -> x64
Architecture x86 -> x86

Thread Safety enabled  -> TS
Thread Safety disabled -> NTS

复制libzmq.dll到php安装目录, 比如d:\php(如果是用apache等其他web服务程序, 就放到执行文件对应的路径, 比如apache/bin)
复制php_zmq.dll到php安装目录\ext文件夹, 比如d:\php\ext

记得php.ini里面要有这句
extension=zmq

记得重启IIS或apache等其他web服务程序

判断有没有安装上:
在phpinfo()页面搜索zmq
或
打开servers.php页面不报错
