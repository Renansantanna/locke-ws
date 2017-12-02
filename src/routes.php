<?php

use Slim\Http\Request;
use Slim\Http\Response;


// Routes


$app->get('/categories', function (Request $request, Response $response) {
		$sql = "SELECT * FROM categories";
		$return = "null";
		try {
			$db = getConnection();
         $sth = $db->prepare($sql);
        $sth->execute();
		$categories = $sth->fetchAll();
		$toJSON = array();
		foreach ($categories as $category) {
			array_push($toJSON, array('id'=>$category['id'],'name'=>utf8_encode($category['name']), 'color'=>$category['color']));
		}
		
        return $this->response->withJson($toJSON);
		} catch(PDOException $e) {
			$return = '{"error":{"text":'. $e->getMessage() .'}}';
		}

    $response->getBody()->write($return);

    return $response;
});

$app->get('/query/{category}', function (Request $request, Response $response) {
	$cat = $request->getAttribute('category');
	if ($cat == 0) {
		$sql = "SELECT * FROM posts";
	} else {
	$sql = "SELECT * FROM posts WHERE subject=:cat";
	}
	$return = "null";
	try {
		$db = getConnection();
		$sth = $db->prepare($sql);
		$sth->bindParam("cat", $cat);
	   $sth->execute();
	   $post = $sth->fetchAll();
	   return $this->response->withJson($post);
	} catch(PDOException $e) {
		$return = '{"error":{"text":'. $e->getMessage() .'}}';
	}

$response->getBody()->write($return);

return $response;
});
$app->get('/query/{category}/{title}', function (Request $request, Response $response) {
	$cat = $request->getAttribute('category');
$title = $request->getAttribute('title');
	if ($cat == 0) {
		$sql = "SELECT * FROM posts where title like '%$title%'";
	} else {
	$sql = "SELECT * FROM posts WHERE subject=:cat && title like '%$title%'";
	}
	$return = "null";
	try {
		$db = getConnection();
		$sth = $db->prepare($sql);
		$sth->bindParam("cat", $cat);
	   $sth->execute();
	   $post = $sth->fetchAll();
	   return $this->response->withJson($post);
	} catch(PDOException $e) {
		$return = '{"error":{"text":'. $e->getMessage() .'}}';
	}

$response->getBody()->write($return);

return $response;
});

$app->get('/post/all', function (Request $request, Response $response) {
		$sql = "SELECT * FROM posts ORDER BY subjectValue ASC";
		$return = "null";
		try {
			$db = getConnection();
         $sth = $db->prepare($sql);
        $sth->execute();
        $posts = $sth->fetchAll();
        return $this->response->withJson($posts);
		} catch(PDOException $e) {
			$return = '{"error":{"text":'. $e->getMessage() .'}}';
		}

    $response->getBody()->write($return);

    return $response;
});



$app->get('/post/{id}', function (Request $request, Response $response) {
$id = $request->getAttribute('id');
		$sql = "SELECT * FROM posts where id=$id";
		$return = "null";
		try {
			$db = getConnection();
         $sth = $db->prepare($sql);
        $sth->execute();
        $post = $sth->fetchAll();
        return $this->response->withJson($post);
		} catch(PDOException $e) {
			$return = '{"error":{"text":'. $e->getMessage() .'}}';
		}

    $response->getBody()->write($return);

    return $response;
});

$app->get('/user-posts/{uid}', function (Request $request, Response $response) {
	$uid = $request->getAttribute('uid');
			$sql = "SELECT * FROM posts where uid like '$uid'";
			$return = "null";
			try {
				$db = getConnection();
			 $sth = $db->prepare($sql);
			$sth->execute();
			$post = $sth->fetchAll();
			return $this->response->withJson($post);
			} catch(PDOException $e) {
				$return = '{"error":{"text":'. $e->getMessage() .'}}';
			}
	
		$response->getBody()->write($return);
	
		return $response;
	});

	$app->get('/user-posts/{uid}/post/{id}', function (Request $request, Response $response) {
		$uid = $request->getAttribute('uid');
		$postId = $request->getAttribute('id');
				$sql = "SELECT * FROM posts where uid like '$uid' && id=$postId";
				$return = "null";
				try {
					$db = getConnection();
				 $sth = $db->prepare($sql);
				$sth->execute();
				$post = $sth->fetchAll();
				return $this->response->withJson($post);
				} catch(PDOException $e) {
					$return = '{"error":{"text":'. $e->getMessage() .'}}';
				}
		
			$response->getBody()->write($return);
		
			return $response;
		});

$app->post('/post', function (Request $request, Response $response) {
		$post = json_decode($request->getBody());
		$sql = "INSERT INTO posts (title, subject, subjectValue, uid, content, color) VALUES(:title, :subject, :subjectValue, :uid, :content, :color)";
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("title", $post->title);
			$stmt->bindParam("subject", $post->subject);
			$stmt->bindParam("subjectValue", $post->subjectValue);
			$stmt->bindParam("uid", $post->uid);
			$stmt->bindParam("content", $post->content);
			$stmt->bindParam("color", $post->color);
			$stmt->execute();
			
			$db = null;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}

});

$app->post('/update-post/{id}', function (Request $request, Response $response) {
	$post = json_decode($request->getBody());
	$postId = $request->getAttribute('id');
	$sql = "UPDATE posts set title = :title, subject = :subject, subjectValue = :subjectValue, uid = :uid, content = :content, color = :color WHERE id=$postId";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("title", $post->title);
		$stmt->bindParam("subject", $post->subject);
		$stmt->bindParam("subjectValue", $post->subjectValue);
		$stmt->bindParam("uid", $post->uid);
		$stmt->bindParam("content", $post->content);
		$stmt->bindParam("color", $post->color);
		$stmt->execute();
		
		$db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}

});

$app->any('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

function getConnection() {
	$dbhost="localhost";
	$dbuser="root";
	$dbpass="123";
	$dbname="locke";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
