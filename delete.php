<?php

if(isset($_GET['id']) && isset($_GET['s']) && is_numeric($_GET['s'])){

	if($_GET['get']==='modules'){
		if($_GET['id'] !== $_SESSION['modules'][$_GET['s']]->MODULE_ID){
			header('Location: 404.html');
			exit();
		}
		$check = $connect->prepare("SELECT MODULE.MODULE_ID FROM `MODULE` WHERE MODULE.MODULE_ID=? LIMIT 1");
        $check->execute(array($_GET['id']));
        if(!$check->rowCount()){
        	header('Location: 404.html');
			exit();
        }else{
        	$check = $connect->prepare("DELETE FROM `MODULE` WHERE MODULE.MODULE_ID=? LIMIT 1");

        	try { 
			    $check->execute(array($_GET['id']));
			    $_SESSION['msg'] = 'The module has been deleted successfully';
			    array_splice($_SESSION['modules'], $_GET['s'], 1);
			    header('Location: ?get='.$_GET['get']);
			    exit();
			} catch (PDOException $e) {
			    if($e->getCode()==='23000'){
			    	$_SESSION['alertError']=true;
			    	$_SESSION['msg'] = 'You cannot delete this module, it contains elements';
			    	header('Location: ?get='.$_GET['get']);
			    	exit();
			    }else{
			    	$_SESSION['alertError']=true;
			    	$_SESSION['msg'] = 'Error While Deleting the module';
			    	header('Location: ?get='.$_GET['get']);
			    	exit();
			    }
			}
        }
	}elseif($_GET['get']==='elements'){
		if($_GET['id'] !== $_SESSION['elements'][$_GET['s']]->ELEMENT_ID){
			header('Location: 404.html');
			exit();
		}
		$check = $connect->prepare("SELECT ELEM_MODULE.ELEMENT_ID FROM `ELEM_MODULE` WHERE ELEM_MODULE.ELEMENT_ID=? LIMIT 1");
        $check->execute(array($_GET['id']));
        if(!$check->rowCount()){
        	header('Location: 404.html');
			exit();
        }else{
        	$check = $connect->prepare("DELETE FROM `ELEM_MODULE` WHERE ELEM_MODULE.ELEMENT_ID=? LIMIT 1");

        	try { 
			    $check->execute(array($_GET['id']));
			    $_SESSION['msg'] = 'The element has been deleted successfully';
			    array_splice($_SESSION['elements'], $_GET['s'], 1);
			    header('Location: ?get='.$_GET['get']);
			    exit();
			} catch (PDOException $e) {
			    if($e->getCode()==='23000'){
			    	$_SESSION['alertError']=true;
			    	$_SESSION['msg'] = 'You cannot delete this element, it contains notes';
			    	header('Location: ?get='.$_GET['get']);
			    	exit();
			    }else{
			    	$_SESSION['alertError']=true;
			    	$_SESSION['msg'] = 'Error While Deleting the element';
			    	header('Location: ?get='.$_GET['get']);
			    	exit();
			    }
			}
        }
	}elseif($_GET['get']==='fields'){
		if($_GET['id'] !== $_SESSION['fields'][$_GET['s']]->FILED_ID){
			header('Location: 404.html');
			exit();
		}
		$check = $connect->prepare("SELECT FILIERE.FILED_ID FROM `FILIERE` WHERE FILIERE.FILED_ID=? LIMIT 1");
        $check->execute(array($_GET['id']));
        if(!$check->rowCount()){
        	header('Location: 404.html');
			exit();
        }else{
        	$check = $connect->prepare("DELETE FROM `FILIERE` WHERE FILIERE.FILED_ID=? LIMIT 1");

        	try { 
			    $check->execute(array($_GET['id']));
			    $_SESSION['msg'] = 'The field has been deleted successfully';
			    array_splice($_SESSION['fields'], $_GET['s'], 1);
			    header('Location: ?get='.$_GET['get']);
			    exit();
			} catch (PDOException $e) {
			    if($e->getCode()==='23000'){
			    	$_SESSION['alertError']=true;
			    	$_SESSION['msg'] = 'You cannot delete this field, it contains modules/users';
			    	header('Location: ?get='.$_GET['get']);
			    	exit();
			    }else{
			    	$_SESSION['alertError']=true;
			    	$_SESSION['msg'] = 'Error While Deleting the field';
			    	header('Location: ?get='.$_GET['get']);
			    	exit();
			    }
			}
        }
	}elseif($_GET['get']==='users'){
		if($_GET['id'] !== $_SESSION['users'][$_GET['s']]->USER_ID){
			header('Location: 404.html');
			exit();
		}
		$check = $connect->prepare("SELECT USER.USER_ID FROM `USER` WHERE USER.USER_ID=? AND USER.USER_TYPE=? LIMIT 1");
        $check->execute(array($_GET['id'],'student'));
        if(!$check->rowCount()){
        	header('Location: 404.html');
			exit();
        }else{
        	$check = $connect->prepare("DELETE FROM `USER` WHERE USER.USER_ID=? AND USER.USER_TYPE=? LIMIT 1");

        	try { 
			    $check->execute(array($_GET['id'],'student'));
			    $_SESSION['msg'] = 'The user has been deleted successfully';
			    array_splice($_SESSION['users'], $_GET['s'], 1);
			    header('Location: ?get='.$_GET['get']);
			    exit();
			} catch (PDOException $e) {
			    if($e->getCode()==='23000'){
			    	$_SESSION['alertError']=true;
			    	$_SESSION['msg'] = 'You cannot delete this user, he/she has notes';
			    	header('Location: ?get='.$_GET['get']);
			    	exit();
			    }else{
			    	$_SESSION['alertError']=true;
			    	$_SESSION['msg'] = 'Error While Deleting the user';
			    	header('Location: ?get='.$_GET['get']);
			    	exit();
			    }
			}
        }
	}else{
		header('Location: 404.html');
		exit();
	}

}else{
	header('Location: 404.html');
	exit();
}