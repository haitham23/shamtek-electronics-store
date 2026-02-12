<?php
require 'admin_layout.php';
require '../bd.php';

$pdo = getBD();

if($_SERVER['REQUEST_METHOD']=='POST'){

$uploadDir = "../images/products/";

$imageName = null;

if(!empty($_FILES['image']['name'])){

    $file = $_FILES['image'];

    
    $allowed = ['jpg','jpeg','png','webp'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if(in_array($ext,$allowed)){

    
        $imageName = uniqid().".".$ext;

        move_uploaded_file(
            $file['tmp_name'],
            $uploadDir.$imageName
        );

        //  DB
        $imageName = "images/".$imageName;

    }
}

$stmt=$pdo->prepare("
INSERT INTO articles
(nom,prix,quantite,url_photo,description,categorie)
VALUES(?,?,?,?,?,?)
");

$stmt->execute([
$_POST['nom'],
$_POST['prix'],
$_POST['quantite'],
$imageName,
$_POST['description'],
$_POST['categorie']
]);

header("Location: manage_products.php");
exit();
}
?>

<h1 class="mb-4">📸 Add Product</h1>

<div class="card shadow p-4" style="max-width:700px;">
<form method="POST" enctype="multipart/form-data">

<label>Name</label>
<input name="nom" class="form-control mb-3" required>

<label>Category</label>
<input name="categorie" class="form-control mb-3">

<label>Price (€)</label>
<input name="prix" type="number" step="0.01" class="form-control mb-3">

<label>Stock</label>
<input name="quantite" type="number" class="form-control mb-3">

<label>Upload Image</label>
<input 
type="file"
name="image"
class="form-control mb-3"
accept="image/*"
onchange="previewFile(event)"
>

<img id="preview" style="max-width:200px;display:none;border-radius:10px;">

<label>Description</label>
<textarea name="description" class="form-control mb-3"></textarea>

<button class="btn btn-success">Add Product</button>
<a href="manage_products.php" class="btn btn-secondary">Cancel</a>

</form>
</div>

<script>
function previewFile(event){

let preview = document.getElementById('preview');
preview.src = URL.createObjectURL(event.target.files[0]);
preview.style.display="block";

}
</script>

<?php require 'admin_footer.php'; ?>