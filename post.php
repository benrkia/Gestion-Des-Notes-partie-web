<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    $response=array('isValide'=>false,'content'=>null);

    if(isset($_POST['post'])){
        require_once('configs/connect.php');
        if($_POST['post']=='signup'){
            $check = $connect->prepare("SELECT USER_ID FROM `USER` WHERE `USER_EMAIL`=?");
			$check->execute(array($_POST['email']));
			if($check->rowCount()){
                $response['content']='User exist already';
				goto endPost;
			}
            
            $check = $connect->prepare("INSERT INTO `USER`(`USER_LNAME`, `USER_FNAME`, `USER_EMAIL`, `USER_PASS`, `USER_CNE`, `FILED_ID`) VALUES (?,?,?,?,?,?)");
            if($check->execute(array($_POST['lname'], $_POST['fname'], $_POST['email'], sha1($_POST['password']), $_POST['cne'], $_POST['field']))){
                $response['isValide']=true;
                $response['content']='User account has been registred succefully';
            }else{
                $response['content']='Error while creating the account';
            }
        }
        elseif($_POST['post']=='signin'){
            $check = $connect->prepare("SELECT * FROM `USER` WHERE `USER_EMAIL`=? AND `USER_PASS`=? LIMIT 1");
			$check->execute(array($_POST['email'], sha1($_POST['password'])));
			if(!$check->rowCount()){
                $response['content']='incorrect username/ password please try again';
				goto endPost;
			}
            
            $response['isValide']=true;
            $response['content']=$check->fetch(PDO::FETCH_OBJ);
            $response['msg']='Welcome back '.$response['content']->USER_FNAME.' '.$response['content']->USER_LNAME;
        }
        elseif($_POST['post']=='addnote'){
            $check = $connect->prepare("INSERT INTO `NOTES`(`NOTE_NAME`, `CHAPT_TITLE`, `CONTENT`, `NOTES`, `ELEMENT_ID`, `USER_ID`) VALUES (?,?,?,?,?,?)");
            
            if($check->execute(array($_POST['NOTE_NAME'], $_POST['CHAPT_TITLE'], $_POST['CONTENT'], $_POST['NOTES'], $_POST['ELEMENT_ID'], $_POST['USER_ID']))){
                $response['isValide']=true;
                $response['content']='Note has been added succefully';
            }else{
                $response['content']='Error while adding the note';
            }
        }
        elseif($_POST['post']=='deletenote'){
            $check = $connect->prepare("DELETE FROM `NOTES` WHERE NOTES.NOTE_ID=?");
            
            if($check->execute(array($_POST['NOTE_ID']))){
                $response['isValide']=true;
                $response['content']='Note has been deleted succefully';
            }else{
                $response['content']='Error while deleting the note';
            }
        }
    }

    endPost:
    echo json_encode($response);