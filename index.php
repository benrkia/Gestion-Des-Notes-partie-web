<?php
	
	require_once('init.php');

    if(!isset($_SESSION['admin'])){

        require_once('./login.html');

        if(isset($_POST['login'])){
            $_email = $_POST['email'];
            $_password = $_POST['password'];
            if(strlen($_password)<8){
                echo "
                    <div class='alert alert-danger alert-dismissable'>
                      <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                      <strong>Error!</strong> The password should be at least 8 chars.
                    </div>
                ";
            }else{
                require_once('configs/connect.php');

                $check = $connect->prepare("SELECT * FROM `USER` WHERE `USER_EMAIL`=? AND `USER_PASS`=? AND `USER_TYPE`=? LIMIT 1");
                $check->execute(array($_email, sha1($_password), 'admin'));
                if(!$check->rowCount()){
                    echo '
                        <div class="alert alert-danger alert-dismissable">
                          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                          <strong>Error!</strong> Email or Password is incorrect.
                        </div>
                    ';
                }else{
                    $_SESSION['admin'] = $check->fetch(PDO::FETCH_OBJ);
                    $_SESSION['msg'] = 'Welcome back '.$_SESSION['admin']->USER_FNAME.' '.$_SESSION['admin']->USER_LNAME;

                    $check = $connect->prepare("SELECT USER.*,FILIERE.FIELD_NAME FROM `USER` INNER JOIN `FILIERE` ON USER.FILED_ID=FILIERE.FILED_ID WHERE USER.USER_TYPE=?");
                    $check->execute(array('student'));
                    $_SESSION['users'] = $check->fetchAll(PDO::FETCH_OBJ);
                    $check = $connect->prepare("SELECT NOTES.*,CONCAT(USER.USER_FNAME, ' ', USER.USER_LNAME) as USER_NAME,ELEM_MODULE.ELEMENT_NAME FROM `NOTES` INNER JOIN USER ON NOTES.USER_ID=USER.USER_ID INNER JOIN ELEM_MODULE ON NOTES.ELEMENT_ID=ELEM_MODULE.ELEMENT_ID");
                    $check->execute(array());
                    $_SESSION['notes'] = $check->fetchAll(PDO::FETCH_OBJ);
                    $check = $connect->prepare("SELECT MODULE.*,FILIERE.FIELD_NAME FROM `MODULE` INNER JOIN `FILIERE` ON MODULE.FILED_ID=FILIERE.FILED_ID");
                    $check->execute(array());
                    $_SESSION['modules'] = $check->fetchAll(PDO::FETCH_OBJ);
                    $check = $connect->prepare("SELECT * FROM `FILIERE`");
                    $check->execute(array());
                    $_SESSION['fields'] = $check->fetchAll(PDO::FETCH_OBJ);
                    $check = $connect->prepare("SELECT ELEM_MODULE.*,MODULE.MODULE_NAME,FILIERE.FILED_ID,FILIERE.FIELD_NAME FROM `ELEM_MODULE` INNER JOIN `MODULE` ON ELEM_MODULE.MODULE_ID=MODULE.MODULE_ID INNER JOIN FILIERE ON MODULE.FILED_ID=FILIERE.FILED_ID");
                    $check->execute(array());
                    $_SESSION['elements'] = $check->fetchAll(PDO::FETCH_OBJ);

                    $check = null;
                    $connect = null;
                    header('Location: index.php');
                }

                $check = null;
                $connect = null;
            }
        }

    }else{

        require_once('configs/connect.php');

        if(isset($_POST['addField'])){

            $_fieldName = $_POST['fieldName'];
            $_moduleName = $_POST['moduleName'];
            $_elementName = $_POST['elementName'];

            if(strlen($_fieldName)<2){
                $_SESSION['alertError']=true;
                $_SESSION['msg']='Field Name must be at least 2 chars';
            }elseif(strlen($_moduleName)<3){
                $_SESSION['alertError']=true;
                $_SESSION['msg']='Module Name must be at least 3 chars';
            }elseif(strlen($_elementName)<3){
                $_SESSION['alertError']=true;
                $_SESSION['msg']='Element Name must be at least 3 chars';
            }else{

                $check = $connect->prepare("INSERT INTO `FILIERE`(`FIELD_NAME`) VALUES (?)");
                
                try {

                    $connect->beginTransaction();

                        $check->execute(array($_fieldName));
                        $_fieldId=$connect->lastInsertId();

                        $check = $connect->prepare("INSERT INTO `MODULE`(`MODULE_NAME`, `FILED_ID`) VALUES (?,?)");
                        $check->execute(array($_moduleName, $_fieldId));
                        $_moduleId=$connect->lastInsertId();

                        $check = $connect->prepare("INSERT INTO `ELEM_MODULE`(`ELEMENT_NAME`, `MODULE_ID`) VALUES (?,?)");
                        $check->execute(array($_elementName, $_moduleId));
                        $_elementId=$connect->lastInsertId();

                    $connect->commit();

                    $_SESSION['msg']='Field with Module and Element of Module has been added successfully';
                    
                    //Update Fields/Modules/Elements session
                    $object = new stdClass();
                    $object->FILED_ID = $_fieldId;
                    $object->FIELD_NAME = $_fieldName;
                    $_SESSION['fields'][] = $object;

                    $object->MODULE_ID = $_moduleId;
                    $object->MODULE_NAME = $_moduleName;
                    $_SESSION['modules'][] = $object;

                    $object->ELEMENT_ID = $_elementId;
                    $object->ELEMENT_NAME = $_elementName;
                    $_SESSION['elements'][] = $object;

                } catch(PDOExecption $e) {
                    $connect->rollback();

                    $_SESSION['alertError']=true;
                    $_SESSION['msg']='Error while creating field';
                }

            }

        }elseif(isset($_POST['addModule'])){

            $_fieldId = $_POST['fieldId'];
            $_moduleName = $_POST['moduleName'];
            $_elementName = $_POST['elementName'];

            $check = $connect->prepare("SELECT `FIELD_NAME` FROM `FILIERE` WHERE FILIERE.FILED_ID=? LIMIT 1");
            $check->execute(array($_fieldId));

            if(!$check->rowCount()){
                $_SESSION['alertError']=true;
                $_SESSION['msg']='You should select a valide Field';
            }elseif(strlen($_moduleName)<3){
                $_SESSION['alertError']=true;
                $_SESSION['msg']='Module Name must be at least 3 chars';
            }elseif(strlen($_elementName)<3){
                $_SESSION['alertError']=true;
                $_SESSION['msg']='Element Name must be at least 3 chars';
            }else{

                $_fieldName = $check->fetch(PDO::FETCH_OBJ)->FIELD_NAME;
                $check = $connect->prepare("INSERT INTO `MODULE`(`MODULE_NAME`, `FILED_ID`) VALUES (?,?)");
                
                try {

                    $connect->beginTransaction();

                        $check->execute(array($_moduleName, $_fieldId));
                        $_moduleId=$connect->lastInsertId();

                        $check = $connect->prepare("INSERT INTO `ELEM_MODULE`(`ELEMENT_NAME`, `MODULE_ID`) VALUES (?,?)");
                        $check->execute(array($_elementName, $_moduleId));
                        $_elementId=$connect->lastInsertId();

                    $connect->commit();

                    $_SESSION['msg']='Module with an Element has been added successfully';
                    
                    //Update Modules/Elements session
                    $object1 = new stdClass();
                    $object1->FILED_ID = $_fieldId;
                    $object1->FIELD_NAME = $_fieldName;
                    $object1->MODULE_ID = $_moduleId;
                    $object1->MODULE_NAME = $_moduleName;
                    $_SESSION['modules'][] = $object1;

                    $object1->ELEMENT_ID = $_elementId;
                    $object1->ELEMENT_NAME = $_elementName;
                    $_SESSION['elements'][] = $object1;

                } catch(PDOExecption $e) {
                    $connect->rollback();

                    $_SESSION['alertError']=true;
                    $_SESSION['msg']='Error while creating Module';
                }

            }
            
        }elseif(isset($_POST['addElement'])){

            $_moduleId = $_POST['moduleId'];
            $_elementName = $_POST['elementName'];

            $check = $connect->prepare("SELECT `MODULE_NAME`,FILIERE.FILED_ID,FILIERE.FIELD_NAME FROM `MODULE` INNER JOIN FILIERE ON MODULE.FILED_ID=FILIERE.FILED_ID WHERE MODULE.MODULE_ID=? LIMIT 1");
            $check->execute(array($_moduleId));

            if(!$check->rowCount()){
                $_SESSION['alertError']=true;
                $_SESSION['msg']='You should select a valide Module';
            }elseif(strlen($_elementName)<3){
                $_SESSION['alertError']=true;
                $_SESSION['msg']='Element Name must be at least 3 chars';
            }else{

                $_module = $check->fetch(PDO::FETCH_OBJ);
                $_moduleName = $_module->MODULE_NAME;
                $_fieldId = $_module->FILED_ID;
                $_fieldName = $_module->FIELD_NAME;

                $check = $connect->prepare("INSERT INTO `ELEM_MODULE`(`ELEMENT_NAME`, `MODULE_ID`) VALUES (?,?)");
                
                try {

                    $connect->beginTransaction();

                        $check->execute(array($_elementName, $_moduleId));
                        $_elementId=$connect->lastInsertId();

                    $connect->commit();

                    $_SESSION['msg']='Element has been added successfully';
                    
                    //Update Elements session
                    $object2 = new stdClass();
                    $object2->FILED_ID = $_fieldId;
                    $object2->FIELD_NAME = $_fieldName;
                    $object2->MODULE_ID = $_moduleId;
                    $object2->MODULE_NAME = $_moduleName;
                    $object2->ELEMENT_ID = $_elementId;
                    $object2->ELEMENT_NAME = $_elementName;
                    $_SESSION['elements'][] = $object2;

                } catch(PDOExecption $e) {
                    $connect->rollback();

                    $_SESSION['alertError']=true;
                    $_SESSION['msg']='Error while creating Module';
                }

            }
            
        }

        $_get = (isset($_GET['get'])) ? $_GET['get'] : 'index';
        $page = '';

        if(isset($_GET['o']) && $_GET['o']==='edit' && isset($_GET['s']) && is_numeric($_GET['s'])){

            switch($_get){
                case 'modules':
                    $page = 'Module';
                    break;
                case 'elements':
                    $page = 'Element';
                    break;
                case 'fields':
                    $page = 'Field';
                    break;
                default:
                    header('Location: 404.html');
                    break;
            }
            require_once('./edit.html');

            if($_SESSION[$_get][$_GET['s']]){

                if(isset($_POST['editField'])){

                    $_fieldName=$_POST['FieldName'];
                    if(strlen($_fieldName)<2){
                        $_SESSION['alertError']=true;
                        $_SESSION['msg']='Field Name must be at least 2 chars';
                    }else{
                        $check = $connect->prepare("UPDATE `FILIERE` SET `FIELD_NAME`=? WHERE FILIERE.FILED_ID=? LIMIT 1");
                        if($check->execute(array($_fieldName, $_SESSION[$_get][$_GET['s']]->FILED_ID))){

                            $_SESSION['msg']='Field has been updated successfully';
                            $_SESSION[$_get][$_GET['s']]->FIELD_NAME = $_fieldName;
                            header('Location: ?get='.$_GET['get']);
                            exit();

                        }else{
                            $_SESSION['alertError']=true;
                            $_SESSION['msg']='Error while updating Field';
                        }
                    }
                }elseif(isset($_POST['editModule'])){

                    $_moduleName=$_POST['ModuleName'];
                    if(strlen($_moduleName)<3){
                        $_SESSION['alertError']=true;
                        $_SESSION['msg']='Module Name must be at least 3 chars';
                    }else{
                        $check = $connect->prepare("UPDATE `MODULE` SET `MODULE_NAME`=? WHERE MODULE.MODULE_ID=? LIMIT 1");
                        if($check->execute(array($_moduleName, $_SESSION[$_get][$_GET['s']]->MODULE_ID))){

                            $_SESSION['msg']='Module has been updated successfully';
                            $_SESSION[$_get][$_GET['s']]->MODULE_NAME = $_moduleName;
                            header('Location: ?get='.$_GET['get']);
                            exit();

                        }else{
                            $_SESSION['alertError']=true;
                            $_SESSION['msg']='Error while updating Module';
                        }
                    }
                }elseif(isset($_POST['editElement'])){

                    $_elementName=$_POST['ElementName'];
                    if(strlen($_elementName)<3){
                        $_SESSION['alertError']=true;
                        $_SESSION['msg']='Element Name must be at least 3 chars';
                    }else{
                        $check = $connect->prepare("UPDATE `ELEM_MODULE` SET `ELEMENT_NAME`=? WHERE ELEM_MODULE.ELEMENT_ID=? LIMIT 1");
                        if($check->execute(array($_elementName, $_SESSION[$_get][$_GET['s']]->ELEMENT_ID))){

                            $_SESSION['msg']='Element has been updated successfully';
                            $_SESSION[$_get][$_GET['s']]->ELEMENT_NAME = $_elementName;
                            header('Location: ?get='.$_GET['get']);
                            exit();

                        }else{
                            $_SESSION['alertError']=true;
                            $_SESSION['msg']='Error while updating Element';
                        }
                    }
                }

            }else{
                header('Location: 404.html');
                exit();
            }

        }else{

            switch($_get){
                case 'modules':
                    require_once('./modules.html');
                    $page = 'Module';
                    break;
                case 'elements':
                    require_once('./elements.html');
                    $page = 'Element';
                    break;
                case 'notes':
                    require_once('./notes.html');
                    break;
                case 'fields':
                    require_once('./fields.html');
                    $page = 'Field';
                    break;
                case 'users':
                    require_once('./users.html');
                    break;
                case 'index':
                    require_once('./home.html');
                    break;
                default:
                    header('Location: 404.html');
                    break;
            }

            if(isset($_GET['o']) && $_GET['o']==='delete'){
                require_once('./delete.php');
            }
        }


        if(isset($_SESSION['msg'])){
            echo '
                <div class="alert alert-'.((isset($_SESSION['alertError']) ? 'danger':'success')).' alert-dismissable">
                  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                  <strong>Alert!</strong> '.$_SESSION['msg'].'.
                </div>
            ';
            unset($_SESSION['alertError']);
            unset($_SESSION['msg']);
        }
    }
