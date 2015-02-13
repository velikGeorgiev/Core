<?php /* 1.0 2015-02-13 20:04:12 CET */ ?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title><?php echo $this->_vars['site']['title']; ?>
</title>
		<?php echo '
		<link rel="stylesheet" href="';  echo $this->_vars['site']['style'];  echo 'style.css" />
		'; ?>

	</head>
	<body>
		<div class="title">
			<img src="<?php echo $this->_vars['site']['img']; ?>
core.png" alt="" /><br />
			<?php echo $this->_vars['name']; ?>
 <?php echo $this->_vars['version']; ?>
 !<br />
			<a href="javascript:;" title="" onclick="Core.hello();" />Version</a>
		</div>

		<pre>
The MIT License (MIT)

Copyright (c) 2015 Velik Georgiev Chelebiev

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
		</pre>
		
		<?php echo '
		<script src="';  echo $this->_vars['site']['script'];  echo 'jquery.min.js" type="text/javascript"></script>
		<script src="';  echo $this->_vars['site']['script'];  echo 'config.js" type="text/javascript"></script>
		<script src="';  echo $this->_vars['site']['script'];  echo 'main.js" type="text/javascript"></script>
		'; ?>

	</body>
</html>