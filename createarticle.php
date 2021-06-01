<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('createarticle.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    
    function checkUploadedFile($filesFieldKey, $log) {
        $retVal = ['', ''];
        
        if (!isset($_FILES[$filesFieldKey])) {
            return $retVal;
        }
    
        $file = $_FILES[$filesFieldKey];
        $fileName = $file['name'];
        $fileNameExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileNameTmp = $file['tmp_name'];
        $fileError = $file['error'];
        $fileSizeBytes = $file['size'];
        $fileType = 'application/' . $fileNameExtension;
        
        if ($fileError == UPLOAD_ERR_OK) {
            if (in_array($fileNameExtension, Constants::ALLOWED_FILE_EXTENSION_UPLOAD)) {
                $retVal[0] = $fileNameTmp;
                $retVal[1] = $fileNameExtension;
                $log->debug('createarticle.php', 'Accepting uploaded file: ' . $fileNameTmp);
            } else {
                $log->warning('createarticle.php', 'Uploaded file extension forbidden: ' . $fileNameExtension);
            }
        }
        
        return $retVal;
    }
    
    $status = 'DATA_VALID';
    $title = '';
    $startingPrice = '';
    $expiresOnDate = '';
    $description = '';
    $image1UploadedFilePath = '';
    $image2UploadedFilePath = '';
    $image3UploadedFilePath = '';
    $image4UploadedFilePath = '';
    $image5UploadedFilePath = '';
    $image1UploadedFileExtension = '';
    $image2UploadedFileExtension = '';
    $image3UploadedFileExtension = '';
    $image4UploadedFileExtension = '';
    $image5UploadedFileExtension = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $titleInput = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
        $startingPriceInput = filter_input(INPUT_POST, 'startingPrice', FILTER_SANITIZE_SPECIAL_CHARS);
        $expiresOnDateInput = filter_input(INPUT_POST, 'expiresOnDate', FILTER_SANITIZE_ENCODED);
        $descriptionInput = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
        $image1UploadedData = checkUploadedFile('image1', $log);
        $image2UploadedData = checkUploadedFile('image2', $log);
        $image3UploadedData = checkUploadedFile('image3', $log);
        $image4UploadedData = checkUploadedFile('image4', $log);
        $image5UploadedData = checkUploadedFile('image5', $log);
        $image1UploadedFilePath = $image1UploadedData[0];
        $image2UploadedFilePath = $image2UploadedData[0];
        $image3UploadedFilePath = $image3UploadedData[0];
        $image4UploadedFilePath = $image4UploadedData[0];
        $image5UploadedFilePath = $image5UploadedData[0];
        $image1UploadedFileExtension = $image1UploadedData[1];
        $image2UploadedFileExtension = $image2UploadedData[1];
        $image3UploadedFileExtension = $image3UploadedData[1];
        $image4UploadedFileExtension = $image4UploadedData[1];
        $image5UploadedFileExtension = $image5UploadedData[1];
        
        if (isset($_POST['title'])) {
            $title = $titleInput;
        } else {
            $status = 'TITLE_MISSING';
        }
        
        if (isset($_POST['startingPrice'])) {
            if ($currencyUtil->getAmountFromCurrencyString($startingPriceInput) != NULL) {
                $startingPrice = $currencyUtil->formatCentsToCurrency($currencyUtil->getAmountFromCurrencyString($startingPriceInput));
            } else {
                $status = 'STARTING_PRICE_MALFORMED';
            }
        } else {
            $status = 'STARTING_PRICE_MISSING';
        }
        
        if (isset($_POST['expiresOnDate'])) {
             if ($dateUtil->checkIfIsValidDateString($expiresOnDateInput)) {
                $expiresOnDate = $expiresOnDateInput;
            } else {
                $status = 'EXPIRES_ON_DATE_MALFORMED';
            }
        } else {
            $status = 'EXPIRES_ON_DATE_MISSING';
        }
        
        if (isset($_POST['description'])) {
            $description = $descriptionInput;
        } else {
            $status = 'DESCRIPTION_MISSING';
        }
        
        if ($image1UploadedFilePath == '') {
            $status = 'IMAGE_1_MISSING';
        }
        
        if ($status == 'DATA_VALID') {
            $result = $articleSystem->addArticle($currentUser, $title, $startingPrice, $expiresOnDate, $description, [$image1UploadedFilePath, $image2UploadedFilePath, $image3UploadedFilePath, $image4UploadedFilePath, $image5UploadedFilePath], [$image1UploadedFileExtension, $image2UploadedFileExtension, $image3UploadedFileExtension, $image4UploadedFileExtension, $image5UploadedFileExtension]);
            if ($result) {
                $status = 'CREATION_SUCCESSFUL';
                $log->debug('createarticle.php', 'Creation of article successful!');
            } else {
                $status = 'CREATION_OF_ARTICLE_FAILED';
                $log->error('createarticle.php', 'Creation of article failed!');
            }
        } else {
            $log->error('createarticle.php', 'Creation of article failed due to missing or malformed mandatory data!');
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $status = 'SHOW_CREATE_ARTICLE_FIELD';
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('createArticle'), array('upload.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    
    echo '<script type="text/javascript">
                function checkUploadFileSize() {
                    var input = document.getElementById("protocol_file");
                    if(input.files && input.files.length == 1) {           
                        if (input.files[0].size > ' . Constants::MAX_UPLOAD_FILE_SIZE_BYTES . ') {
                            alert("' . $i18n->get('uploadWillFailAsMaximumFileSizeIs') . ' " + (' . Constants::MAX_UPLOAD_FILE_SIZE_BYTES . '/1024/1024) + "MB.");
                            return false;
                        }
                    }
                    return true;
                }
            </script>';
    
    function getCreateItemField($currentUser, $dateUtil, $currencyUtil, $i18n, $title, $startingPrice, $expiresOnDate, $description, $titleColor, $startingPriceColor, $expiresOnDateColor, $image1Color, $descriptionColor) {
        return '<form enctype="multipart/form-data" action="createarticle.php" method="POST" onsubmit="checkUploadFileSize();">
                    <input type="hidden" name="MAX_FILE_SIZE" value="' . Constants::MAX_UPLOAD_FILE_SIZE_BYTES . '" />
                    <table style="width: 100%; padding: 0px;">
                        <tr>
                            <td style="width: 40%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <label for="title">' . $i18n->get('articleTitle') . ':</label>
                            </td>
                            <td style="width: 60%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <input type="text" id="title" name="title" size="4" style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; background-color: ' . $titleColor . ';" value="' . $title . '" placeholder="' . $i18n->get('articleTitle') . '" checked>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 40%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <label for="startingPrice">' . $i18n->get('startingPrice') . ':</label>
                            </td>
                            <td style="width: 60%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <input type="text" id="startingPrice" name="startingPrice" size="4" style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; background-color: ' . $startingPriceColor . ';" value="' . $startingPrice . '" placeholder="1,00 €" checked>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 40%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <label for="expiresOnDate">' . $i18n->get('expiresOnDate') . ':</label>
                            </td>
                            <td style="width: 60%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <input type="text" id="expiresOnDate" name="expiresOnDate" size="4" style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; background-color: ' . $expiresOnDateColor . ';" value="' . $expiresOnDate . '" placeholder="' . $dateUtil->getDateFormatString($currentUser->getLanguage()) . '" checked>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 40%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <label for="image1">' . $i18n->get('image1') . ':</label>
                            </td>
                            <td style="width: 60%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <input type="file" id="image1" name="image1" placeholder="" style="display: table-cell; width: calc(100% - 15px); background-color: ' . $image1Color . ';" checked>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 40%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <label for="image2">' . $i18n->get('image2') . ' (' . $i18n->get('optional') . '):</label>
                            </td>
                            <td style="width: 60%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <input type="file" id="image2" name="image2" placeholder="" style="display: table-cell; width: calc(100% - 15px);">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 40%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <label for="image3">' . $i18n->get('image3') . ' (' . $i18n->get('optional') . '):</label>
                            </td>
                            <td style="width: 60%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <input type="file" id="image3" name="image3" placeholder="" style="display: table-cell; width: calc(100% - 15px);">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 40%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <label for="image4">' . $i18n->get('image4') . ' (' . $i18n->get('optional') . '):</label>
                            </td>
                            <td style="width: 60%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <input type="file" id="image4" name="image4" placeholder="" style="display: table-cell; width: calc(100% - 15px);">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 40%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <label for="image5">' . $i18n->get('image5') . ' (' . $i18n->get('optional') . '):</label>
                            </td>
                            <td style="width: 60%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <input type="file" id="image5" name="image5" placeholder="" style="display: table-cell; width: calc(100% - 15px);">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 40%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <label for="description">' . $i18n->get('description') . ':</label>
                            </td>
                            <td style="width: 60%; padding: 20px 20px 0px 20px; vertical-align: center;">
                                <textarea id="description" name="description" cols="40" rows="5" style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; background-color: ' . $descriptionColor . '; font-family: Arial; font-size: 16px;" placeholder="' . $i18n->get('description') . '" checked>' . $description . '</textarea>
                            </td>
                        </tr>
                    </table>
                    <center><input type="submit" value="' . $i18n->get('submit') . '" style="margin: 40px 20px 20px 20px;"></center>
                </form>';
    }

    $message = '';
    $content = '';
    
    $showCreateField = true;
    
    $titleColor = 'white';
    $startingPriceColor = 'white';
    $expiresOnDateColor = 'white';
    $image1Color = 'white';
    $descriptionColor = 'white';
    if ($status == 'TITLE_MISSING') {
        $titleColor = 'red';
    }
    if ($status == 'STARTING_PRICE_MALFORMED' || $status == 'STARTING_PRICE_MISSING') {
        $startingPriceColor = 'red';
    }
    if ($status == 'EXPIRES_ON_DATE_MALFORMED' || $status == 'EXPIRES_ON_DATE_MISSING') {
        $expiresOnDateColor = 'red';
    }
    if ($status == 'DESCRIPTION_MISSING') {
        $descriptionColor = 'red';
    }
    if ($status == 'IMAGE_1_MISSING') {
        $image1Color = 'red';
    }
    
    if ($status == 'CREATION_SUCCESSFUL') {
        $message = '<br><br><center>' . $i18n->get('articleWasCreatedSuccessfully') . '<br><br><br><a id="styledButton" href="createarticle.php">' . $i18n->get('createAnotherArticle') . '</a></center>';
        $showCreateField = false;
    } else if ($status == 'CREATION_OF_ARTICLE_FAILED') {
        $message = $i18n->get('creationOfArticleFailed');
        $showCreateField = false;
    }
    
    if ($showCreateField) {
        $content = getCreateItemField($currentUser, $dateUtil, $currencyUtil, $i18n, $title, $startingPrice, $expiresOnDate, $description, $titleColor, $startingPriceColor, $expiresOnDateColor, $image1Color, $descriptionColor);
    }
    
    if ($message != '') {
        $message = $message . '<br><br>';
    }

    echo '<div style="margin-left: 20%; width: 60%; padding-top: 30px;">
                <div id="uploadField">
                    <center><br><a id="styledButton" href="articles.php">&laquo;&nbsp;' . $i18n->get('backToArticlesList') . '</a><br><br></center>'
                    . $message . $content . 
                '</div>
            </div>';

    echo $footer->getFooter();
?>
