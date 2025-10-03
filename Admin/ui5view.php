 <!DOCTYPE html>
<html lang="en">
<head>
	<title>document</title>
</head>
<style>
	form{
		border: 3px solid #f1f1f1;
	}
	input[type=text],input[type=password]{
		width: 100%;
		padding: 12px 20px;
		margin: 8px 0;
		display: inline-block;
		border: 1px solid #ccc;
		box-sizing: border-box;
	}
	button{
		background-color: #04AA60;
		color: white;
		padding: 14px 20px;
		margin: 8px 0;
		border: none;
		cursor: pointer;
		width: 100%;
	}
	button_hover{
		opacity: 0.8;
	}
	.cancelbtn{
		width: auto;
		padding: 10px 18px;
		background-color: #f44336;

	}
	.container{
		padding: 16 px;

	}
	span.psw{
		float: right;
		padding-top: 16px;
	}
	@media screen and (max-width: 300px){
		span.psw{
			display: block;
			float: none;
		}
		.cancelbtn{
			width: 100%;
		}
	}
</style>
	<body>
<form action="ui5update.php" method="POST">
	<div class="container">
		<label for="id"><b>id</b></label><br>
			<input type="number"placeholder="Enter id"name="id"id="id"required><br>
			<lable for="username"><b>USERNAME</b></lable><br>
			<input type="text"placeholder="Enter username"name="username"id="username" required>
			<label for="email"><b>EMAIL</b></label><br>
			<input type="text" placeholder="Enter EMAIL" name="email" id="email" required>
			<label for="phone"><b>PHONE</b></label><br>
			<input type="text" placeholder="Enter PHONE" name="phone" id="phone" required>
			<label for="address"><b>ADDRESS</b></label><br>
			<input type="text" placeholder="Enter ADDRESS" name="address" id="address" required>
			<label for="password"><b>PASSWORD</b></label><br>
			<input type="text" placeholder="Enter PASSWORD" name="password" id="password" required>
			<label for="confirmpassword"><b>CONFIRMPASSWORD</b></label><br>
			<input type="text" placeholder="Enter CONFIRMPASSWORD" name="confirmpassword" id="confirmpassword" required>
			<button type="submit">UPDATE</button>
		</div>
	</form>
</body>
<script src="jquery_link.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#id").focus();
		$("#id").keyup(function(){
			var id=$("#id").val();
			$.ajax({
				url:"search3.php",
				method:"GET",
				data:{id:id},
				success:function(response){
					response=JSON.parse(response);
					$("#username").val(response.username);
					$("#email").val(response.email);
					$("#phone").val(response.phone);
					$("#address").val(response.address);
					$("#password").val(response.password);
					$("#confirmpassword").val(response.confirmpassword);
				}
			});
		});
	});
</script>
</html