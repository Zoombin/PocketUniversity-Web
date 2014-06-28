        $info  = Image::getImageInfo($image);
         if($info !== false) {
            $srcWidth  = $info['width'];
            $srcHeight = $info['height'];
            $pathinfo = pathinfo($image);
			$type =  $pathinfo['extension'];
            $type = empty($type)?$info['type']:$type;
			$type	=	strtolower($type);
            $interlace  =  $interlace? 1:0;
            unset($info);
            // 载入原图
            $createFun = 'ImageCreateFrom'.($type=='jpg'?'jpeg':$type);
            $srcImg     = $createFun($image);

            //创建缩略图
            if($type!='gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($maxWidth, $maxHeight);
            else
                $thumbImg = imagecreate($maxWidth, $maxHeight);

            // 新建PNG缩略图通道透明处理
            if('png'==$type) {
                imagealphablending($thumbImg, false);//取消默认的混色模式
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
            }elseif('gif'==$type) {
            // 新建GIF缩略图预处理，保证透明效果不失效
	            $background_color  =  imagecolorallocate($thumbImg,  0,255,0);  //  指派一个绿色
	            imagecolortransparent($thumbImg,$background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }

			// 计算缩放比例
			if(($maxWidth/$maxHeight)>=($srcWidth/$srcHeight)){
				//宽不变,截高，从中间截取 y=
				$width	=	$srcWidth;
				$height	=	$srcWidth*($maxHeight/$maxWidth);
				$x		=	0;
				$y		=	($srcHeight-$height)*0.5;
			}else{
				//高不变,截宽，从中间截取，x=
				$width	=	$srcHeight*($maxWidth/$maxHeight);
				$height	=	$srcHeight;
				$x		=	($srcWidth-$width)*0.5;
				$y		=	0;
			}
			// 复制图片
			if(function_exists("ImageCopyResampled")){
				ImageCopyResampled($thumbImg, $srcImg, 0, 0, $x, $y, $maxWidth, $maxHeight, $width,$height);
			}else{
				ImageCopyResized($thumbImg, $srcImg, 0, 0, $x, $y, $maxWidth, $maxHeight,  $width,$height);
			}
			ImageDestroy($srcImg);
			/*水印开始* /
			if($warterMark){
				//计算水印的位置,默认居中
				$textInfo = Image::getImageInfo($warterMark);
				$textW	=	$textInfo["width"];
				$textH	=	$textInfo["height"];
				unset($textInfo);
				$mark = imagecreatefrompng($warterMark);
				$imgW	=	$width;
				$imgH	=	$width*$textH/$textW;
				$y		=	($height-$textH)/2;
				if(function_exists("ImageCopyResampled")){
					ImageCopyResampled($thumbImg,$mark,0,$y,0,0, $imgW,$imgH, $textW,$textH);
				}else{
					ImageCopyResized($thumbImg,$mark,0,$y,0,0,$imgW,$imgH,  $textW,$textH);
				}
				ImageDestroy($mark);
			}
			/*水印结束*/
            /*if('gif'==$type || 'png'==$type) {
				//imagealphablending($thumbImg, FALSE);//取消默认的混色模式
                //imagesavealpha($thumbImg,TRUE);//设定保存完整的 alpha 通道信息
                $background_color  =  ImageColorAllocate($thumbImg,  0,255,0);
				//  指派一个绿色
				imagecolortransparent($thumbImg,$background_color);
				//  设置为透明色，若注释掉该行则输出绿色的图
            }*/

            // 对jpeg图形设置隔行扫描
            if('jpg'==$type || 'jpeg'==$type) 	imageinterlace($thumbImg,$interlace);

            // 生成图片
            //$imageFun = 'image'.($type=='jpg'?'jpeg':$type);
            $imageFun	=	'imagepng';
			$filename  = empty($filename)? substr($image,0,strrpos($image, '.')).$suffix.'.'.$type : $filename;

            $imageFun($thumbImg,$filename);
            ImageDestroy($thumbImg);
            return $filename;
         }
         return false;