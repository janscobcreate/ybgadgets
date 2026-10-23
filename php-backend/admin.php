<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_ok()) { http_response_code(403); $error='This form expired. Refresh and try again.'; }
    else {
      $action=(string)($_POST['action']??'');
      if ($action==='login') {
        $user=(string)($_POST['username']??'');
        $pass=(string)($_POST['password']??'');
        $validHash=isset($config['admin_password_hash']) && str_starts_with($config['admin_password_hash'],'$2') || str_starts_with($config['admin_password_hash']??'','$argon2');
        if ($validHash && hash_equals($config['admin_username'],$user) && password_verify($pass,$config['admin_password_hash'])) {
            session_regenerate_id(true); $_SESSION['admin']=true;header('Location: admin.php');exit;
        }
        usleep(250000);$error='Invalid username or password.';
      } elseif ($action==='logout') { $_SESSION=[];session_destroy();header('Location: admin.php');exit; }
      elseif (logged_in()) {
        try {
          if ($action==='save_product') {
            $id=trim((string)($_POST['id']??'')) ?: uuid();
            $name=trim((string)($_POST['name']??''));$category=trim((string)($_POST['category']??''));$image=trim((string)($_POST['image']??''));
            if (!empty($_FILES['image_file']['name'])) {
              $file=$_FILES['image_file'];
              if ($file['error']!==UPLOAD_ERR_OK || $file['size']>4*1024*1024 || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Image upload failed or exceeds 4MB.');
              $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
              $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/avif'=>'avif'];
              if (!isset($allowed[$mime]) || !getimagesize($file['tmp_name'])) throw new RuntimeException('Only valid JPEG, PNG, WebP or AVIF images are accepted.');
              $uploadDir=__DIR__.'/uploads';
              if (!is_dir($uploadDir) && !mkdir($uploadDir,0755,true)) throw new RuntimeException('Upload directory unavailable');
              $filename=bin2hex(random_bytes(16)).'.'.$allowed[$mime];
              if (!move_uploaded_file($file['tmp_name'],$uploadDir.'/'.$filename)) throw new RuntimeException('Image could not be stored');
              $image='uploads/'.$filename;
            }
            $price=filter_var($_POST['price']??null,FILTER_VALIDATE_INT);$stock=filter_var($_POST['stock']??null,FILTER_VALIDATE_INT);
            if (!$name||!$category||(!filter_var($image,FILTER_VALIDATE_URL)&&!preg_match('#^uploads/[a-f0-9]{32}\.(jpg|png|webp|avif)$#',$image))||$price===false||$price<1||$stock===false||$stock<0) throw new RuntimeException('Please provide valid product details.');
            $old=($_POST['old_price']??'')===''?null:(int)$_POST['old_price'];
            db()->prepare("INSERT INTO products (id,name,category,price,old_price,image,description,badge,stock,featured) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),category=VALUES(category),price=VALUES(price),old_price=VALUES(old_price),image=VALUES(image),description=VALUES(description),badge=VALUES(badge),stock=VALUES(stock),featured=VALUES(featured)")->execute([substr($id,0,100),substr($name,0,150),substr($category,0,60),(int)$price,$old,substr($image,0,1500),substr((string)($_POST['description']??''),0,2000),substr((string)($_POST['badge']??''),0,50),(int)$stock,isset($_POST['featured'])?1:0]);
          } elseif ($action==='delete_product') {
            db()->prepare('DELETE FROM products WHERE id=?')->execute([(string)($_POST['id']??'')]);
          } elseif ($action==='status') {
            $table=(string)($_POST['table']??'');$status=(string)($_POST['status']??'');
            $allowed=['orders'=>['Pending','Confirmed','Processing','Shipped','Completed','Cancelled'],'swaps'=>['New','Reviewing','Contacted','Completed','Declined'],'inquiries'=>['New','In progress','Resolved']];
            if (!isset($allowed[$table])||!in_array($status,$allowed[$table],true)) throw new RuntimeException('Invalid status');
            db()->prepare("UPDATE `$table` SET status=? WHERE id=?")->execute([$status,(string)($_POST['id']??'')]);
          }
          header('Location: admin.php?tab='.urlencode((string)($_POST['tab']??'products')));exit;
        } catch (Throwable $ex) { $error=$ex->getMessage(); }
      }
    }
}
$tab=(string)($_GET['tab']??'overview');
if (!in_array($tab,['overview','products','orders','swaps','inquiries'],true)) $tab='overview';
function fmt($n): string { return '₦'.number_format((float)$n); }
function field(string $label,string $name,$value='',string $type='text',bool $required=false): void {
    echo '<label>'.e($label).'<input type="'.e($type).'" name="'.e($name).'" value="'.e($value).'" '.($required?'required':'').'></label>';
}
function status_form(string $table,array $row,array $choices): void {
  global $tab;
  echo '<form class="inline" method="post"><input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'"><input type="hidden" name="action" value="status"><input type="hidden" name="table" value="'.e($table).'"><input type="hidden" name="tab" value="'.e($tab).'"><input type="hidden" name="id" value="'.e($row['id']).'"><select name="status" onchange="this.form.submit()">';
  foreach ($choices as $x) echo '<option '.($row['status']===$x?'selected':'').'>'.e($x).'</option>';
  echo '</select></form>';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>YB Gadgets Admin</title><style>
:root{font-family:Arial,Helvetica,sans-serif;color:#16303c}*{box-sizing:border-box}body{margin:0;background:#f3f7f8}button,input,select,textarea{font:inherit}button{cursor:pointer}a{text-decoration:none;color:inherit}.login{max-width:430px;margin:9vh auto;background:white;padding:38px;border-radius:9px;box-shadow:0 14px 45px #0b293013}.login h1{margin:10px 0;font-size:27px}.login p{color:#80919a;font-size:13px;margin-bottom:30px}label{display:flex;flex-direction:column;gap:7px;font-size:12px;font-weight:bold;margin-bottom:15px}input,select,textarea{background:#fcfefe;border:1px solid #dce6ea;border-radius:4px;padding:12px;outline:0;width:100%;color:#16303c}input:focus,textarea:focus{border-color:#09b5d2}.btn{background:#102d3b;color:white;border:0;padding:13px 20px;font-weight:bold;border-radius:4px}.btn:hover{background:#078ca8}.wide{width:100%}.alert{padding:12px;background:#fdeeee;color:#a32e43;border-radius:5px;font-size:12px;margin:15px 0}.app{display:flex;min-height:100vh}.side{width:230px;background:#0c222d;color:white;padding:26px 18px;flex-shrink:0}.side h2{font-size:18px}.side small{font-size:9px;letter-spacing:1.6px;color:#80b4c3}.side nav{margin-top:35px}.side nav a{display:block;padding:14px;border-radius:5px;color:#a6c0ca;font-size:13px}.side nav a.active,.side nav a:hover{background:#184958;color:white}.side footer{margin-top:50px}.side footer button{color:#a6c0ca;background:none;border:0;padding:12px}.main{flex:1;min-width:0;padding:40px 4%}.main h1{margin:0 0 7px;font-size:32px;letter-spacing:-1px}.sub{font-size:13px;color:#8a9ca4;margin-bottom:30px}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:15px}.stat,.card{background:white;border:1px solid #e1e9ed;border-radius:6px;padding:23px}.stat span{font-size:11px;color:#79929d}.stat b{display:block;font-size:34px;margin-top:17px}.grid{display:grid;grid-template-columns:1.2fr 1fr;gap:20px}.table-scroll{width:100%;overflow:auto}.card{margin-top:18px}h3{font-size:17px;margin:0 0 20px}table{border-collapse:collapse;width:100%;font-size:12px;text-align:left}th{color:#8fa0a8;font-size:10px;letter-spacing:1px}td,th{padding:14px 12px;border-bottom:1px solid #edf1f3;vertical-align:top}td small{display:block;color:#8ea1aa;margin-top:4px}td img{width:52px;height:52px;object-fit:cover;border-radius:4px}td select{padding:7px;font-size:11px;min-width:130px}td input{padding:8px}.inline{display:inline}.inline button{background:#edf8fa;border:0;color:#007e98;padding:8px 12px;border-radius:4px}.danger{color:#ac4c59!important}.product-form{display:grid;grid-template-columns:1fr 1fr;gap:11px}.product-form .full{grid-column:span 2}.product-form h3{grid-column:span 2}.product-form label{margin:0}.checkbox{display:flex;align-items:center;flex-direction:row}.checkbox input{width:auto}.muted{color:#8399a1;font-size:13px}.back-link{display:block;margin-top:25px;color:#89baca;font-size:12px}@media(max-width:860px){.side{width:170px}.main{padding:25px 3%}.stats{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.app{display:block}.side{width:100%;padding:16px}.side nav{margin-top:10px;display:flex;overflow:auto}.side nav a{white-space:nowrap}.side footer{margin:8px 0}.side footer button{padding:5px}.main{padding:25px 4%}.product-form{grid-template-columns:1fr}.product-form .full,.product-form h3{grid-column:1}}
</style></head><body>
<?php if (!logged_in()): ?>
<div class="login"><div style="color:#08aeca;font-weight:900;letter-spacing:2px">YB GADGETS.</div><h1>Admin sign in</h1><p>Manage product inventory, incoming orders and customer enquiries.</p><?php if($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="login"><?php field('Username','username','', 'text',true);field('Password','password','','password',true);?><button class="btn wide">Sign in →</button></form></div>
<?php else:
$p=db()->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
$o=db()->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
$s=db()->query('SELECT * FROM swaps ORDER BY created_at DESC')->fetchAll();
$m=db()->query('SELECT * FROM inquiries ORDER BY created_at DESC')->fetchAll();
$edit=null;if(isset($_GET['edit']))foreach($p as $item)if($item['id']===$_GET['edit'])$edit=$item;
?>
<div class="app"><aside class="side"><h2>YB GADGETS.</h2><small>ADMIN CONTROL PANEL</small><nav><?php foreach(['overview'=>'Overview','products'=>'Products','orders'=>'Orders','swaps'=>'Swap requests','inquiries'=>'Customer messages'] as $key=>$title):?><a class="<?=$tab===$key?'active':''?>" href="?tab=<?=e($key)?>"><?=e($title)?></a><?php endforeach; ?></nav><footer><form method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="logout"><button>Sign out →</button></form></footer><a class="back-link" href="../">← Back to storefront</a></aside><main class="main"><h1><?=e(ucfirst($tab==='inquiries'?'Customer messages':$tab))?></h1><p class="sub">Your secure PHP / MySQL store workspace.</p><?php if($error): ?><div class="alert"><?=e($error)?></div><?php endif;?>
<?php if($tab==='overview'):?><div class="stats"><div class="stat"><span>Products</span><b><?=count($p)?></b></div><div class="stat"><span>Order requests</span><b><?=count($o)?></b></div><div class="stat"><span>Swap enquiries</span><b><?=count($s)?></b></div><div class="stat"><span>Support messages</span><b><?=count($m)?></b></div></div><div class="card"><h3>Recent order requests</h3><?php if(!$o): ?><p class="muted">New orders will show here.</p><?php else: ?><div class="table-scroll"><table><tr><th>Customer</th><th>Amount</th><th>Status</th><th>Received</th></tr><?php foreach(array_slice($o,0,8) as $r):?><tr><td><?=e($r['customer'])?></td><td><?=fmt($r['total'])?></td><td><?=e($r['status'])?></td><td><?=e($r['created_at'])?></td></tr><?php endforeach;?></table></div><?php endif;?></div><?php endif;?>
<?php if($tab==='products'): ?><div class="card"><form class="product-form" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="save_product"><input type="hidden" name="tab" value="products"><input type="hidden" name="id" value="<?=e($edit['id']??'')?>"><h3><?=$edit?'Edit product':'Add a new product'?></h3><?php field('Product name','name',$edit['name']??'','text',true); ?><label>Category<select name="category"><?php foreach(['Phones','Laptops','Gaming','Accessories','Tablets'] as $c):?><option <?=($edit['category']??'')===$c?'selected':''?>><?=e($c)?></option><?php endforeach;?></select></label><?php field('Price (₦)','price',$edit['price']??'','number',true);field('Old price (₦)','old_price',$edit['old_price']??'','number');field('Stock quantity','stock',$edit['stock']??1,'number',true);field('Badge','badge',$edit['badge']??'');?><div class="full"><?php field('Image URL (optional if uploading)','image',$edit['image']??'','text');?></div><label class="full">Or upload a product photo (up to 4MB)<input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/avif"></label><label class="full">Description<textarea name="description" rows="3"><?=e($edit['description']??'')?></textarea></label><label class="checkbox full"><input type="checkbox" name="featured" <?=!empty($edit['featured'])?'checked':''?>> Featured item</label><div class="full"><button class="btn">Save product →</button></div></form></div><div class="card"><h3>Product catalogue (<?=count($p)?>)</h3><div class="table-scroll"><table><tr><th>Image</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr><?php foreach($p as $r):?><tr><td><img src="<?=e($r['image'])?>" alt=""></td><td><strong><?=e($r['name'])?></strong></td><td><?=e($r['category'])?></td><td><?=fmt($r['price'])?></td><td><?=e($r['stock'])?></td><td><a href="?tab=products&amp;edit=<?=urlencode($r['id'])?>">Edit</a> <form class="inline" method="post" onsubmit="return confirm('Delete this product?')"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="tab" value="products"><input type="hidden" name="id" value="<?=e($r['id'])?>"><button class="danger">Delete</button></form></td></tr><?php endforeach;?></table></div></div><?php endif;?>
<?php if($tab==='orders'): ?><div class="card"><h3>Orders (<?=count($o)?>)</h3><div class="table-scroll"><table><tr><th>Customer</th><th>Items</th><th>Total</th><th>Delivery</th><th>Status</th></tr><?php foreach($o as $r):?><tr><td><?=e($r['customer'])?><small><?=e($r['email'])?> · <?=e($r['phone'])?></small><small><?=e($r['created_at'])?></small></td><td><?php $items=json_decode($r['items'],true)?:[];foreach($items as $it)echo e($it['name']??'Item').' × '.e($it['qty']??1).'<br>';?></td><td><?=fmt($r['total'])?></td><td><?=e($r['address'])?></td><td><?php status_form('orders',$r,['Pending','Confirmed','Processing','Shipped','Completed','Cancelled']);?></td></tr><?php endforeach;?></table></div></div><?php endif;?>
<?php if($tab==='swaps'): ?><div class="card"><h3>Swap requests (<?=count($s)?>)</h3><div class="table-scroll"><table><tr><th>Customer</th><th>Current device</th><th>Desired device</th><th>Condition / notes</th><th>Status</th></tr><?php foreach($s as $r):?><tr><td><?=e($r['name'])?><small><?=e($r['phone'])?></small></td><td><?=e($r['current_device'])?></td><td><?=e($r['desired_device'])?></td><td><?=e($r['device_condition'])?><small><?=e($r['details'])?></small></td><td><?php status_form('swaps',$r,['New','Reviewing','Contacted','Completed','Declined']);?></td></tr><?php endforeach;?></table></div></div><?php endif;?>
<?php if($tab==='inquiries'): ?><div class="card"><h3>Customer messages (<?=count($m)?>)</h3><div class="table-scroll"><table><tr><th>Customer</th><th>Message</th><th>Received</th><th>Status</th></tr><?php foreach($m as $r):?><tr><td><?=e($r['name'])?><small><?=e($r['contact'])?></small></td><td><?=e($r['message'])?></td><td><?=e($r['created_at'])?></td><td><?php status_form('inquiries',$r,['New','In progress','Resolved']);?></td></tr><?php endforeach;?></table></div></div><?php endif;?>
</main></div><?php endif;?></body></html>
