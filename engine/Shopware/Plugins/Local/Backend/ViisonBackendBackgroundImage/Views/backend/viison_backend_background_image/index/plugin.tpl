{block name="backend/base/header/css" append}
<style type="text/css">

	.x-viewport, .x-viewport body, body {
	   background: url({$viisonBackgroundImage}) no-repeat center center fixed !important;
		-webkit-background-size: cover;
		-moz-background-size: cover;
		-o-background-size: cover;
		background-size: cover;
	}

	body:after {
	    z-index: -1px;
	    content: '';
	    display: block;
	    position: fixed;
	    top: 0;
	    left: 0;
	    width: 100%;
	    height: 100%;
	    background: url({$viisonLogo}) no-repeat bottom left;
	    pointer-events:none;
	}

	.login-window[style] {
	    margin-top: -120px;
	}

</style>
{/block}

