<?php
// Optional PHP-hosted JSON API. The static Sites preview uses local browser data.
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function json_response(array $data, int $status=200): void { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE); exit; }
function value($v, int $max=300): string { return mb_substr(trim(is_string($v)?$v:''),0,$max); }
function rows(string $table): array { return db()->query("SELECT * FROM `$table` ORDER BY created_at DESC")->fetchAll(); }
try {
  if ($_SERVER['REQUEST_METHOD']==='GET') {
    if (($_GET['mode']??'')==='admin') {
      require_admin();
      json_response(['products'=>rows('products'),'orders'=>rows('orders'),'swaps'=>rows('swaps'),'inquiries'=>rows('inquiries')]);
    }
    json_response(['products'=>rows('products')]);
  }
  if ($_SERVER['REQUEST_METHOD']!=='POST') json_response(['error'=>'Method not allowed'],405);
  $body=json_decode(file_get_contents('php://input') ?: '{}',true);
  if (!is_array($body)) json_response(['error'=>'Invalid JSON'],400);
  $type=value($body['type']??'',30);
  $id=uuid();
  if ($type==='order') {
    $customer=value($body['customer']??'',150);$email=value($body['email']??'',190);$phone=value($body['phone']??'',45);$address=value($body['address']??'',1000);
    $items=$body['items']??null;
    if (!$customer || !filter_var($email,FILTER_VALIDATE_EMAIL) || !$phone || !$address || !is_array($items) || count($items)<1 || count($items)>30) json_response(['error'=>'Invalid order details'],422);
    $selected=[];$total=0;
    $stmt=db()->prepare('SELECT id,name,price,stock FROM products WHERE id=?');
    foreach ($items as $item) {
      if (!is_array($item)) json_response(['error'=>'Invalid item'],422);
      $qty=(int)($item['qty']??0);
      if ($qty<1 || $qty>20) json_response(['error'=>'Invalid quantity'],422);
      $stmt->execute([value($item['id']??'',100)]);$p=$stmt->fetch();
      if (!$p || $qty>(int)$p['stock']) json_response(['error'=>'Product unavailable'],409);
      $selected[]=['id'=>$p['id'],'name'=>$p['name'],'price'=>(int)$p['price'],'qty'=>$qty];$total+=(int)$p['price']*$qty;
    }
    $q=db()->prepare("INSERT INTO orders (id,customer,email,phone,address,items,total,status) VALUES (?,?,?,?,?,?,?,'Pending')");
    $q->execute([$id,$customer,$email,$phone,$address,json_encode($selected),$total]);
    json_response(['ok'=>true,'id'=>$id,'total'=>$total],201);
  }
  if ($type==='swap') {
    $name=value($body['name']??'',150);$phone=value($body['phone']??'',45);$current=value($body['currentDevice']??'',160);$desired=value($body['desiredDevice']??'',160);$condition=value($body['condition']??'',70);$details=value($body['details']??'',2000);
    if (!$name||!$phone||!$current||!$desired||!$condition) json_response(['error'=>'Missing required fields'],422);
    db()->prepare("INSERT INTO swaps (id,name,phone,current_device,desired_device,device_condition,details) VALUES (?,?,?,?,?,?,?)")->execute([$id,$name,$phone,$current,$desired,$condition,$details]);json_response(['ok'=>true,'id'=>$id],201);
  }
  if ($type==='support') {
    $name=value($body['name']??'',150);$contact=value($body['contact']??'',190);$message=value($body['message']??'',4000);
    if (!$name||!$contact||!$message) json_response(['error'=>'Missing required fields'],422);
    db()->prepare('INSERT INTO inquiries (id,name,contact,message) VALUES (?,?,?,?)')->execute([$id,$name,$contact,$message]);json_response(['ok'=>true,'id'=>$id],201);
  }
  require_admin();
  // Admin JSON mutations require a same-origin CSRF header.
  if (!hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_CSRF_TOKEN']??'')) json_response(['error'=>'Invalid CSRF token'],403);
  if ($type==='product') {
    $p=$body['product']??[];$pid=value($p['id']??'',100) ?: $id;$name=value($p['name']??'',150);$cat=value($p['category']??'',60);$price=filter_var($p['price']??null,FILTER_VALIDATE_INT);$image=value($p['image']??'',1500);$stock=filter_var($p['stock']??null,FILTER_VALIDATE_INT);
    if (!$name||!$cat||$price===false||$price<1||$stock===false||$stock<0||!filter_var($image,FILTER_VALIDATE_URL)) json_response(['error'=>'Invalid product'],422);
    $old=$p['oldPrice']??null;$old=($old===null||$old==='')?null:(int)$old;
    db()->prepare("INSERT INTO products (id,name,category,price,old_price,image,description,badge,stock,featured) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),category=VALUES(category),price=VALUES(price),old_price=VALUES(old_price),image=VALUES(image),description=VALUES(description),badge=VALUES(badge),stock=VALUES(stock),featured=VALUES(featured)")->execute([$pid,$name,$cat,$price,$old,$image,value($p['description']??'',2000),value($p['badge']??'',50),$stock,empty($p['featured'])?0:1]);
    json_response(['ok'=>true]);
  }
  if ($type==='status') {
    $table=value($body['table']??'',20);$allowed=['orders'=>['Pending','Confirmed','Processing','Shipped','Completed','Cancelled'],'swaps'=>['New','Reviewing','Contacted','Completed','Declined'],'inquiries'=>['New','In progress','Resolved']];
    $status=value($body['status']??'',40);
    if (!isset($allowed[$table])||!in_array($status,$allowed[$table],true)) json_response(['error'=>'Invalid status'],422);
    db()->prepare("UPDATE `$table` SET status=? WHERE id=?")->execute([$status,value($body['id']??'',36)]);
    json_response(['ok'=>true]);
  }
  json_response(['error'=>'Unknown action'],400);
} catch (Throwable $e) { error_log('YB API error: '.$e->getMessage()); json_response(['error'=>'Unable to process request'],500); }
