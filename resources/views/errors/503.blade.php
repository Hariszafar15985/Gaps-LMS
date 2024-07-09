<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Lorem</title>


    <style>
      body { text-align: center; padding: 20px; font: 20px Helvetica, sans-serif; color: #000; }
      @media (max-width: 768px){
        .logo-img img {
        width: 60%;
        }
      }
      @media (max-width: 500px){
        .logo-img img {
        width: 100%;
        }
      }

      h1 { font-size: 50px; }
      article { display: block; text-align: left; max-width: 650px; margin: 0 auto; }
      a { color: #dc8100; text-decoration: none; }
      a:hover { color: #efe8e8; text-decoration: none; }
      body{ padding-top: 100px; }

      .logo-img img {
        width: 70%;
        }


    </style>

  </head>
  <body bgcolor="#fff">
    <article>
        <div class="logo-img"><img src={{asset('assets/maintenance/logo.png')}} alt="Lorem" srcset=""></div>
        <h1>Optimizing Your Learning Journey!</h1>
        <div>
            <p>Hang tight! We're fine-tuning our educational hub to bring you an even more immersive learning experience. Stay tuned - we'll be back soon with an improved experience!</p>
        </div>
    </article>

  </body>
</html>
