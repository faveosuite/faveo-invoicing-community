var myApp = angular.module('myApp', []);

myApp.controller('MainController', ['$http', '$scope',
    function($http, $scope) {
        //        $http.get('url').success(function(data) {
        //
        //        });

        //titile and content for page 3
        $scope.Databasetitle = 'Database Type';
        $scope.Databasecontent = 'Choose the type of your database';

        $scope.Hosttitle = 'MySQL Host';
        $scope.Hostcontent = 'If your MySQL is installed on the same server as Helpdesk, let it be localhost';

        $scope.Porttitle = 'Database Port number';
        $scope.Portcontent = 'Port number on which your MySQL server is listening. By default, it is 3306';

        
        //titile and content for page 4

        $scope.Nametitle = 'First Name';
        $scope.Namecontent = 'System administrator first name';
        
        $scope.Lasttitle = 'Last Name';
        $scope.Lastcontent = 'System administrator last name';
        
        $scope.Emailtitle = 'Email';
        $scope.Emailcontent = 'Email Double-check your email address before continuing';
        
        $scope.UserNametitle = 'Username';
        $scope.UserNamecontent = 'Username can have only alphanumeric characters, spaces, underscores, hyphens, periods, and the @ symbol.';
        
        $scope.Passtitle = 'Password';
        $scope.Passcontent = 'Important: You will need this password to log in. Please store it in a secure location.';
        
        $scope.Confirmtitle = 'Confirm Password';
        $scope.Confirmcontent = 'Type the same password as above';
        
        $scope.Languagetitle = 'Helpdesk Language';
        $scope.Languagecontent = 'The language you want to run Helpdesk in';
        
        $scope.Timezonetitle = 'Time Zone';
        $scope.Timezonecontent = 'Helpdesk default time zone';

        $scope.Datetimetitle = 'Helpdesk Date & Time format';
        $scope.Datetimecontent = 'What format you want to display date & time in Helpdesk';

        $scope.DummyDataTitle = 'Helpdesk Dummy Data';
        $scope.DummyDataContent = 'Check this checkbox if you want to install and test Helpdesk with dummy data. You can clear dummy data and start using Helpdesk in production anytime.';

        $scope.EnvTitle = 'Helpdesk App Environment';
        $scope.EnvContent = 'If you select environment as testing/development (available only in enterprise versions), make sure you have composer installed on the server. You must run "composer dumputoload" in Helpdesk root directory before installation. Otherwise system will not work.';

        $scope.StorageTitle = 'Helpdesk Default Storage';
        $scope.StorageContent = 'If `S3` is selected the files in the system will be stored in S3 cloud storage, whereas if `system` is selected files will be stored in local filesystem';
        //MySQL SSL connection details
        $scope.SSLConnectiontitle = "Connect securely to database over SSL/TLS";
        $scope.SSLConnectionSettingcontent = "Update these settings if your database server allows secure connection over SSL/TLS to transmit encrypted data. While using these settings make sure all the required files are present and accessible by the system. All files must be in pem bundle format. Note: Please leave these settings empty if database can be connected without SSL to avoid unexpected errors.";
        $scope.SSLCAtitle = "SSL Certificate Authority Bundle";
        $scope.SSLCAcontent = "Full path of SSL Certificate Authority Bundle file in pem format present on the server.";
        $scope.SSLCerttitle = "SSL Certificate File";
        $scope.SSLCertcontent = "Full path of SSL Certificate file in pem format present on the server.";
        $scope.SSLKeytitle = "SSL Certificate Key File";
        $scope.SSLKeycontent = "Full path of SSL Certificate Key file in pem format present on the server.";
        $scope.VerifySSLtitle = "Verify SSL Certificate";
        $scope.VerifySSLcontent = "Verify SSL Peer's Certificates";
    }
]);
