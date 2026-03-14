let products = JSON.parse(localStorage.getItem("products")) || [];

function addProduct(){

let name=document.getElementById("name").value;
let sku=document.getElementById("sku").value;
let category=document.getElementById("category").value;
let qty=document.getElementById("qty").value;

let product={name,sku,category,qty};

products.push(product);

localStorage.setItem("products",JSON.stringify(products));

displayProducts();
updateDashboard();
}

function displayProducts(){

let table=document.getElementById("productTable");

if(!table) return;

table.innerHTML=`
<tr>
<th>Name</th>
<th>SKU</th>
<th>Category</th>
<th>Quantity</th>
</tr>
`;

products.forEach(p=>{

let row=table.insertRow();

row.insertCell(0).innerHTML=p.name;
row.insertCell(1).innerHTML=p.sku;
row.insertCell(2).innerHTML=p.category;
row.insertCell(3).innerHTML=p.qty;

});

}

function updateDashboard(){

let total=document.getElementById("totalProducts");

if(total){
total.innerText=products.length;
}

}

displayProducts();
updateDashboard();