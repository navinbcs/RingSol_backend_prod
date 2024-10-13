<?php
//  echo "123";exit;
   // Retrieve posted data
   $posted        = file_get_contents("php://input");

   // Decode from JSON
   $obj           =  json_decode($posted);

   // Retrieve the file name and string data
   $fileName      =  strip_tags($obj->name);
   $fileName1     =  explode('.', $fileName);
   $fileNameFinal =  $fileName1[0].".txt";
   $fileData      =  strip_tags($obj->data);



   try {

      // Open a file stream/pointer
      $handler       = fopen('upload/' . $fileNameFinal, "a+");


      // Attempt to write the string data to the specified file pointer
      if(!fwrite($handler, $fileData))
      {
         echo json_encode(array('message' => 'Error! The supplied data was NOT written to ' . $fileNameFinal));
      }

      // If all has gone well attempt to close the file stream
      if(!fclose($handler))
      {
         echo json_encode(array('message' => 'Error! The file was not closed properly!'));
      }

      // If we get this far the operation has succeeded - let the user know :)
      echo json_encode(array('message' => 'The file ' . $fileNameFinal . ' was successfully uploaded'));

   } 
   catch(Exception $e)
   {
      echo json_encode(array('message' => 'Fail!'));
   }


?>