<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends CI_Controller {

    public function __construct() {
        parent::__construct(); 
        $this->load->library('fcm', [
            'serviceAccountKeyFile' => 'table-saint-firebase-adminsdk-parxu-57d79df06e.json', // Replace with the path to your service account key file
            'projectID' => 'table-saint' // Replace with your Firebase project ID
        ]);
		
		
    }

    /**
     * Send Notification Example
     */
    public function send() {
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjE2MSIsImRhdGEiOnsiUGF0aWVudElkIjoiMTYxIiwiTW9iaWxlQ29kZSI6Iis5MSAgICAgICAiLCJNb2JpbGVOdW1iZXIiOiI4MDg3ODczNTcwIiwiT1RQIjoiNzg4MzQxIiwiRnVsbE5hbWUiOiJOYXZpbiBUaGFrdXIgIiwiRW1haWwiOiJuYXZpbmJjc0BnbWFpbC5jb20iLCJBdnRhciI6bnVsbCwiRGF0ZU9mQmlydGgiOiIyMDAwLTAxLTAxIDAwOjAwOjAwLjAwMCIsIkdlbmRlcklkIjpudWxsLCJBZGRyZXNzIjoiYjIgNTAxIGthdGUiLCJCbG9vZEdyb3VwSWQiOjYsIkluc2VydERhdGUiOiIyMDIyLTEwLTEyIDE5OjIzOjQ1LjAwMCIsIlVwZGF0ZURhdGUiOm51bGwsIklzQWN0aXZlIjoxLCJDb3VudHJ5TWFzdGVySWQiOm51bGwsIlN0YXRlTWFzdGVySWQiOm51bGwsIkNpdHlNYXN0ZXJJZCI6bnVsbCwiUGluQ29kZSI6bnVsbCwiTGFzdE5hbWUiOiIiLCJNUk5vIjpudWxsLCJUZW5hbnRJZCI6bnVsbCwiSWRlbnRpZmljYXRpb25UeXBlSWQiOm51bGwsIklkZW50aXR5SXNzdWVDb3VudHJ5SWQiOm51bGwsIkNhdXNlT2ZEZWF0aElkIjpudWxsLCJOYXRpb25hbGl0eUlkIjpudWxsLCJIb21lQ29kZSI6bnVsbCwiT2ZmaWNlQ29kZSI6bnVsbCwiSWRlbnRpdHlFeHBpcnlEYXRlIjpudWxsLCJEZWF0aERhdGUiOm51bGwsIklzRGVjZWFzZWQiOm51bGwsIlBybiI6IlBSTi0wMDAxNjEiLCJSZWZlcmVuY2VObyI6bnVsbCwiUGF0aWVudEltYWdlIjpudWxsLCJJZGVudGl0eU5vIjpudWxsLCJIb21lUGhvbmUiOm51bGwsIk9mZmljZVBob25lIjpudWxsLCJSZW1hcmtzIjpudWxsLCJJbnNlcnRVc2VySWQiOm51bGwsIkF1dG9Qcm4iOm51bGwsIklzQXV0byI6bnVsbCwiVXBkYXRlVXNlcklkIjpudWxsLCJNb2JpbGVDb2RlSWQiOm51bGwsIkZpcnN0TmFtZSI6Ik5hdmluIFRoYWt1ciJ9LCJpYXQiOjE3MjYwNjkzMDYsImV4cCI6MTcyNjA4NzMwNn0.ICnKsESMiizwaCKssnOqXyJQKvuQM7ZW3pHMyFnZfBs'; // Replace with the recipient's device token
        $token = 'cankw4YMQMqz0Ya8_klTTo:APA91bEUnF2W6TBplcTEw-6kODjTYOAJXyLGfrmxGdTSfBmcOOOGrMzznNExSNSp5CILFDo5OTSUdWgdx7tYlHt2osUmA9nttuSyZp8K4H-DRiggGueQysypTVTEt6uXvyBmcB4UeY7E';      
	    $title = 'Test Notification';
        $body = 'This is a test notification  By Navin';

        $response = $this->fcm->sendNotification($token, $title, $body);

        echo $response;
    }
}
