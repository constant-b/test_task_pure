<?php
require_once "../../utils/UploadedFile.php";


foreach ($_FILES as $key => $value) {
    $file = UploadedFile::getFile($key);

    $dirName  = "../uploads/" . date("Y") . "/" . date("m") . "/";
    $fileName = UploadedFile::generateRandomName($file->getExtension());

    echo json_encode(["success" => $file->save($dirName . $fileName)]);
}
