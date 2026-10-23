/* Shared catalogue and state for all GitHub Pages storefront routes. */
window.YBCatalog=(()=>{
const sample=[
['iphone-15-pro','iPhone 15 Pro','Phones',975000,1100000,'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&q=85','Titanium design. Pro-level performance. 128GB.','BEST SELLER',6,1],
['macbook-air','MacBook Air M2','Laptops',1250000,1390000,'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&q=85','13-inch Liquid Retina display, Apple M2, 8GB RAM.','POPULAR',4,1],
['samsung-s24','Samsung Galaxy S24 Ultra','Phones',1320000,null,'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&q=85','Galaxy AI, S Pen and incredible camera.','NEW ARRIVAL',3,1],
['dell-xps','Dell XPS 13','Laptops',890000,1020000,'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=800&q=85','Compact premium Windows ultrabook.','GREAT VALUE',5,1],
['airpods-pro','AirPods Pro (2nd Gen)','Accessories',215000,255000,'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=800&q=85','Active Noise Cancellation and spatial audio.','HOT DEAL',12,0],
['ipad-air','iPad Air','Tablets',665000,null,'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&q=85','Versatile tablet for everything you do.','',7,0],
['hp-spectre','HP Spectre x360','Laptops',980000,null,'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&q=85','Flexible touchscreen laptop with standout style.','',4,0],
['watch','Apple Watch Series 9','Accessories',385000,420000,'https://images.unsplash.com/photo-1434494878577-86c23bcb06b9?w=800&q=85','Stay connected, active and healthy.','SALE',8,0],
['ps5','PlayStation 5 Slim','Gaming',795000,860000,'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=800&q=85','Next-generation console gaming. 1TB storage.','GAMING',5,1],
['sony-headphones','Sony WH-1000XM5','Accessories',345000,395000,'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&q=85','Wireless noise-cancelling over-ear headphones.','HOT DEAL',11,0],
['iphone-13','iPhone 13','Phones',485000,540000,'https://images.unsplash.com/photo-1632633173522-3ff5e6e7bcb7?w=800&q=85','Super Retina XDR display, dual camera, 128GB.','GREAT VALUE',9,0],
['lenovo-thinkpad','Lenovo ThinkPad X1 Carbon','Laptops',750000,null,'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&q=85','Lightweight business laptop for serious work.','',3,0],
['ps5-controller','DualSense Wireless Controller','Gaming',108000,125000,'https://images.unsplash.com/photo-1600080972464-8e5f35f63d08?w=800&q=85','Immersive haptic feedback for PlayStation 5.','',14,0],
['jbl-speaker','JBL Flip 6 Bluetooth Speaker','Accessories',135000,null,'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&q=85','Portable wireless sound with powerful bass.','',10,0]
].map((x,i)=>({id:x[0],name:x[1],category:x[2],price:x[3],oldPrice:x[4],image:x[5],description:x[6],badge:x[7],stock:x[8],featured:x[9],createdAt:i+1}));
const keys={products:'yb-gadgets-products-v1',cart:'yb-gadgets-cart-v1',orders:'yb-gadgets-orders-v1',swaps:'yb-gadgets-swaps-v1',messages:'yb-gadgets-messages-v1'};
const read=(key,fallback=[])=>{try{return JSON.parse(localStorage.getItem(keys[key])||'null')??fallback}catch{return fallback}};
const write=(key,value)=>localStorage.setItem(keys[key],JSON.stringify(value));
const products=()=>{const custom=read('products');return [...custom,...sample.filter(p=>!custom.some(x=>x.id===p.id))]};
const money=value=>'₦'+Number(value).toLocaleString('en-NG');
const esc=value=>String(value??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const add=(id,qty=1)=>{const p=products().find(x=>x.id===id);if(!p||p.stock<1)return false;const cart=read('cart'),item=cart.find(x=>x.id===id);if(item)item.qty=Math.min(p.stock,item.qty+qty);else cart.push({id,qty:Math.min(qty,p.stock)});write('cart',cart);return true};
const recommendations=(product,limit=4)=>{const list=products().filter(p=>p.id!==product.id&&p.stock>0);return [...list.filter(p=>p.category===product.category).sort((a,b)=>b.featured-a.featured),...list.filter(p=>p.category!==product.category).sort((a,b)=>b.featured-a.featured)].slice(0,limit)};
return {sample,read,write,products,money,esc,add,recommendations};
})();
