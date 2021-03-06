<?php
header('Content-type: application/json');

require_once __DIR__ . '/config.php';

use core\Routes as Router;
use core\Pagination as Pagination;


class index extends Pagination
{

    private $host = '127.0.0.1';
    private $user = 'root';
    private $password = '';
    private $database = 'rest_api';

    private function getConnect()
    {
        $connect = new mysqli(
            $this->host,
            $this->user,
            $this->password,
            $this->database
        );


        return $connect;
    }

    public function viewPosts(int $page = 1)
    {
        $connect = $this->getConnect();

        $this->getPagination($connect, 6, 'posts', $page);

        $jsonArray = array();
        $jsonArray['pagination'] = $this->pagination;


        while ($post = $this->query->fetch_assoc()) {
            $jsonArray['posts'][] = [
                'id' => (int) $post['id'],
                'title' => $post['title'],
                'category' => $post['category'],
                'query' => mb_substr($post['query'], 0, 200) . "..."
            ];
        }

        echo json_encode($jsonArray);
        $connect->close();

    }


    public function viewPost(int $id)
    {
        $connect = $this->getConnect();

        $post = $connect->query("SELECT * FROM `posts` WHERE `id` = {$id}")->fetch_assoc();

        if (!$post) {

            http_response_code(404);
            echo json_encode([
                'status' => false,
                'message' => 'Post not found'
            ]);

            $connect->close();
        } else {

            $jsonArray['post'] = [
                'post_id' => (int) $post['id'],
                'datetime' => $post['datetime'],
                'title' => $post['title'],
                'category' => $post['category'],
                'query' => $post['query']
            ];

            echo json_encode($jsonArray);

        }

        $connect->close();
    }


    public function addPost()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $connect = $this->getConnect();

            $data = $_POST;

            if (empty($data['title']) || empty($data['category']) || empty($data['query'])) {
                http_response_code(204);
                echo json_encode([
                    'status' => false
                ]);

            } else {
                $connect->query("INSERT INTO `posts` (`id`, `title`, `datetime`, `category`, `query`) VALUES ".
                    "(NULL, '{$data['title']}', current_timestamp(), '{$data['category']}', '{$data['query']}'); ");

                http_response_code(201);
                echo json_encode([
                    'status' => true,
                    'message' => 'New post has been created',
                    'post_id' => $connect->insert_id
                ]);
            }

            $connect->close();
        } else {
            http_response_code(405);
            echo json_encode([
                'status' => false,
                'message' => 'This is not a POST method'
            ]);
        }

    }


    public function editPost(int $id)
    {

        if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {

            $connect = $this->getConnect();

            $checkValidPost = $connect->query("SELECT `id` FROM `posts` WHERE `id` = '{$id}'");
            if (!$checkValidPost->fetch_assoc()) {

                http_response_code(405);
                echo json_encode([
                    'status' => false,
                    'message' => 'Post with this id not found'
                ]);

            } else {

                $data = file_get_contents('php://input');
                $data = json_decode($data, true);

                if (empty($data["title"]) || empty($data["category"]) || empty($data["query"])) {
                    http_response_code(204);
                    echo json_encode([
                        'status' => false
                    ]);

                } else {

                    $data = [htmlspecialchars($data['title']), htmlspecialchars($data['category']), htmlspecialchars($data['query'])];
                    $connect->query("UPDATE `posts` SET `title` = '{$data[0]}', `category` = '{$data[1]}', ".
                        "`query` = '{$data[2]}' WHERE `posts`.`id` = $id ");

                    http_response_code(202);
                    echo json_encode([
                        'status' => true,
                        'post_id' => $id
                    ]);
                }

            }

            $connect->close();

        } else {
            http_response_code(405);
            echo json_encode([
                'status' => false,
                'message' => 'This is not a PATCH method'
            ]);
        }

    }


    public function deletePost(int $id)
    {

        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

            $connect = $this->getConnect();
            $checkValidPost = $connect->query("SELECT `id` FROM `posts` WHERE `id` = '{$id}'");

            if (!$checkValidPost->fetch_assoc()) {

                http_response_code(204);
                echo json_encode([
                    'status' => false
                ]);

            } else {
                http_response_code(200);
                $connect->query("DELETE FROM `posts` WHERE `id` = '{$id}'");
                echo json_encode([
                    'status' => true,
                    'message' => 'Post with id '.$id.' deleted'
                ]);

            }


        } else {

            http_response_code(405);
            echo json_encode([
                'status' => false,
                'message' => 'This is not a DELETE method'
            ]);

        }

    }


}


Router::route('/', function () {
    $new = new index();
    $new->viewPosts();
});

Router::route('/post/new', function () {
    $new = new index();
    $new->addPost();
});

Router::route('/post/delete/(\w+)', function (int $id) {
    $new = new index();
    $new->deletePost($id);
});

Router::route('/post/update/(\w+)', function (int $id) {
    $new = new index();
    $new->editPost($id);
});

Router::route('/page/(\w+)', function (int $page) {
    $new = new index();
    $new->viewPosts($page);
});

Router::route('/post/(\w+)', function (int $id) {
    $new = new index();
    $new->viewPost($id);
});


Router::execute($_SERVER['REQUEST_URI']);

