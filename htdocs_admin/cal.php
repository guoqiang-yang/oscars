<?php

	$PRODUCTS = array(
		//desc是商品描述，1是电梯每单位的价格，2是楼梯每单位每层的价格
		array('desc' => '沙子(20公斤)', 1 => 0.7, 2 => 0.5, 'unit' => '袋'),
		array('desc' => '水泥', 1 => 1, 2 => 1, 'unit' => '袋'),
		array('desc' => '20公斤腻子粉', 1 => 0.7, 2 => 0.7, 'unit' => '袋'),
		array('desc' => '40公斤粉类', 1 => 1, 2 => 1, 'unit' => '袋'),
		array('desc' => '大芯板，石膏板', 1 => 1, 2 => 1, 'unit' => '张'),
		array('desc' => '矿棉板', 1 => 1, 2 => 1, 'unit' => '包'),
		array('desc' => '轻体砖5cm', 1 => 0.2, 2 => 0.2, 'unit' => '块'),
		array('desc' => '轻体砖8cm', 1 => 0.3, 2 => 0.3, 'unit' => '块'),
		array('desc' => '轻体砖10cm', 1 => 0.4, 2 => 0.4, 'unit' => '块'),
		array('desc' => '轻体砖15cm', 1 => 0.6, 2 => 0.6, 'unit' => '块'),
		array('desc' => '轻体砖20cm', 1 =>0.8, 2 => 0.8, 'unit' => '块'),
		array('desc' => '桶装(17KG-20KG)', 1 => 1, 2 => 1, 'unit' => '桶'),
		array('desc' => '铁管(4米)', 1 => 1, 2 => 1, 'unit' => '捆'),
		array('desc' => '轻钢龙骨(3米)', 1 => 1, 2 => 1, 'unit' => '捆'),
		array('desc' => '石膏线', 1 => 1, 2 => 1, 'unit' => '捆'),
	);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>搬运费计算</title>
		<style type="text/css">
			.form-control {
				display: block;
				width: 100%;
				height: 34px;
				padding: 6px 12px;
				font-size: 14px;
				line-height: 1.42857143;
				color: #555;
				background-color: #fff;
				background-image: none;
				border: 1px solid #ccc;
				border-radius: 4px;
				-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
				box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
				-webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
				-o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
				transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
			}
			.btn-primary {
				color: #fff;
				background-color: #337ab7;
				border-color: #2e6da4;
			}
			.btn {
				display: inline-block;
				padding: 6px 12px;
				margin-bottom: 0;
				font-size: 14px;
				font-weight: 400;
				line-height: 1.42857143;
				text-align: center;
				white-space: nowrap;
				vertical-align: middle;
				-ms-touch-action: manipulation;
				touch-action: manipulation;
				cursor: pointer;
				-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none;
				user-select: none;
				background-image: none;
				border: 1px solid transparent;
				border-radius: 4px;
			}
		</style>
	</head>
	<body style="text-align: center;">
		<?php foreach ($PRODUCTS as $k => $product) { ?>
			<div style="width: 40%; margin-left:5%; float: left;" class="form-group">
				<label style="float: left; height: 48px; line-height: 48px; margin-bottom: 10px;">
					<?php echo $product['desc'] . '（电梯：' . $product[1] . '元/' . $product['unit'] .'；楼梯：' . $product[2] . '元/' . $product['unit'] . '/层 ）' ?>
				</label>

				<input data-id="<?php echo $k ?>" data-price1="<?php echo $product['1'] ?>" data-price2="<?php echo $product['2'] ?>" type="text" class="form-control product_num" value="" style="float: right; width:80px;display:inline-block;"  />
			</div>
		<?php } ?>
		<div style="clear: both;"></div>
		<div style="margin-top: 20px;" class="form-group">
			<select id="stair_type" class="form-control carry_type" name="type" style="margin-right:20px;display:inline-block;width:150px;">
				<option value="1">电梯</option>
				<option value="2">楼梯</option>
			</select>

			楼层：<input style="margin-right:20px;width: 80px;display: inline-block;" class="form-control" type="text" id="floor-num" value="1" />
			总价：<input style="margin-right:20px;width: 80px;display: inline-block;" id="total-price" class="form-control" value="0" />
		</div>
		<div style="margin-top: 20px;" class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button style="font-size: 20px; width: 100px;" type="button" class="btn btn-primary" id="cal_btn">计算</button>
			</div>
		</div>
	</body>
	<script src="http://sa.haocaisong.cn/js/jquery.min.js">
	</script>
	<script>
		$(function () {
			$('#cal_btn').on('click', function() {
				var total = 0;

				var numList = $('.product_num');
				var len = numList.length;

				var floorNum = parseInt($('#floor-num').val());
				if (floorNum <= 0 || isNaN(floorNum)) {
					alert('还能不能愉快地玩耍了？？楼层写得对么？？');
					return false;
				}

				var type = $('#stair_type').val();

				for (var i = 0; i < len; i++) {
					var num = parseInt($(numList[i]).val());
					if (num <= 0 || isNaN(num)) {
						continue;
					}

					var id = $(numList[i]).data('id');

					var price  = 0;
					if (type == 1) {
						price = parseFloat($(numList[i]).data('price1'));
						floorNum = 1;
					} else if (type == 2) {
						price = parseFloat($(numList[i]).data('price2'));
					}

					total += floorNum * num * price;
				}

				$('#total-price').val(total);
			});
		})
	</script>
</html>