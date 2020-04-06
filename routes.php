<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

// Interface
$app->get('/', function (Request $request, Response $response, array $args) {
    
    $data = array(
    		"main_heading"=>"Welcome to Slim 3 framework for Beginner",
    		"sub_heading" => "In this tutorial we are going to learn slim 3 framework"
    		);
			
		
    return $this->view->render($response, 'homepage.php',$data);
   

});

$app->get('/about',function(Request $request, Response $response, array $args){

	$data = array(
    		"main_heading"=>"About Us",
    		"sub_heading" => "In this tutorial we are going to learn slim 3 framework"
    		);

    return $this->view->render($response, 'about.php',$data);
});

$app->get('/blog',function(Request $request, Response $response, array $args){
	
	$qry = "select * from blog";
	$rs = $this->db->query($qry);
	
	
	while($row = $rs->fetch_assoc()){
			$blogEntries[] = $row;
		}
	
	$data = array(
		"main_heading" => "Blog",
		"blog_entries" =>$blogEntries
	);
	
	return $this->view->render($response, 'blog.php',$data);
});

// Restful API
$app->post("/users/", function(Request $request, Response $response) {
        $user = $request->getParsedBody();

        $sql = "SELECT * FROM users WHERE email=:email OR name=:name ";
        $stmt = $this->db->prepare($sql);
        
        $params = [
            ":email" => $user["clue"],
            ":name" => $user["clue"]
        ];
        
        $stmt->execute($params);
        $result = $stmt->fetch();

        if($result > 0) {

            $id = $result["user_id"];
            $isVerified = password_verify($user["password"], $result["password"]);

            if($isVerified) {

                $SqlLog = "UPDATE users SET last_login=NOW() WHERE user_id=:user_id";
                $stmtLog = $this->db->prepare($SqlLog);

                $paramsLog = [
                    ":user_id" => $id                    
                ];
                
                if($stmtLog->execute($paramsLog)) {
                    return $response->withJson(["status" => "success", "data" => $result], 200);
                }
                
            } else {
                return $response->withJson(["status" => "failed", "data" => "0"], 200);
            }

        } else {
            return $response->withJson(["status" => "failed", "data" => "user unknow"], 200);
        }
    });

$app->post("/users/new/", function(Request $request, Response $response) {
        $new_user = $request->getParsedBody();

        $sql = "INSERT INTO users (name, lastname, password, email, phone, is_active)
                VALUE (:name, :lastname, :password, :email, :phone, :is_active)";
        $stmt = $this->db->prepare($sql);
        
        $data = [
            ":name" => $new_user["name"], 
            ":lastname" => $new_user["lastname"], 
            ":password" => password_hash( $new_user["password"], PASSWORD_DEFAULT), 
            ":email" => $new_user["email"], 
            ":phone" => $new_user["phone"], 
            ":is_active" => "1"
        ];

        if($stmt->execute($data)) {
            return $response->withJson(["status" => "success", "data" => "1"], 200);
        } else {
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        }
    });

    $app->get("/reservasi/", function(Request $request, Response  $response, array $args) {
        $keyword    = $request->getQueryParam("date");

        $sql    = "SELECT 	r.rsv_id, rd.rsv_det_id, p.plgn_nmlengkap, rd.rsv_nama_mtr, rsv_det_helm,
                            rd.rsv_det_jashujan,r.total, r.rsv_jam_mulai AS jam, rd.monitor = 0 AS monitor, r.note
                    FROM 	pelanggan p,
                            reservasi_detail rd,
                            reservasi r
                    WHERE	r.rsv_tgl_mulai = '$keyword'
                            AND r.rsv_id = rd.rsv_id
                            AND r.plgn_no_identitas = p.plgn_no_identitas
                            AND r.rsv_status='APPROVE'
                    UNION
                    SELECT 	r.rsv_id, rd.rsv_det_id, p.plgn_nmlengkap, rd.rsv_nama_mtr, rsv_det_helm,
                            rd.rsv_det_jashujan,r.total, r.rsv_jam_selesai AS jam, rd.monitor = 1 AS monitor, r.note
                    FROM 	pelanggan p,
                            reservasi_detail rd,
                            reservasi r
                    WHERE	r.rsv_tgl_selesai = '$keyword'
                            AND r.rsv_id = rd.rsv_id
                            AND r.plgn_no_identitas = p.plgn_no_identitas
                            AND r.rsv_status='APPROVE'  
                    ORDER BY `jam`  ASC";
                    
        $stmt   = $this->db->prepare($sql);

        $stmt -> execute();
        $result = $stmt -> fetchAll();
            
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });
