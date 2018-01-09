<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');

    $response=array('content'=>false);

    if(isset($_GET['get'])){
        require_once('configs/connect.php');
        if($_GET['get']=='fields'){
            $check = $connect->prepare("SELECT * FROM `FILIERE`");
			$check->execute();
			if(!$check->rowCount()){
				goto endGet;
			}
            $response['content']=$check->fetchAll(PDO::FETCH_OBJ);
        }
        elseif($_GET['get']=='notes'){
            $check = $connect->prepare("SELECT NOTES.*,ELEM_MODULE.ELEMENT_NAME FROM `NOTES` INNER JOIN `ELEM_MODULE` ON NOTES.ELEMENT_ID=ELEM_MODULE.ELEMENT_ID WHERE `USER_ID`=? ORDER BY NOTE_ID DESC");
			$check->execute(array($_GET['user']));
			if(!$check->rowCount()){
				$response['notes']['exist']=false;
				$response['notes']['content']='Your Notes memo still empty';
			}else{
				$response['notes']['exist']=true;
                $response['notes']['content']=$check->fetchAll(PDO::FETCH_OBJ);
            }
            
            $stmt = $connect->prepare("SELECT MODULE.MODULE_ID,MODULE.MODULE_NAME,ELEM_MODULE.ELEMENT_ID,ELEM_MODULE.ELEMENT_NAME FROM `MODULE` INNER JOIN `ELEM_MODULE` ON MODULE.MODULE_ID=ELEM_MODULE.MODULE_ID WHERE MODULE.FILED_ID=?");
			$stmt->execute(array($_GET['field']));
			if($stmt->rowCount()){
                $response['content']=$stmt->fetchAll(PDO::FETCH_OBJ);
			}
            
        }
    }

    endGet:
    echo json_encode($response);