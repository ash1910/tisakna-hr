<style>
	td.box {
		white-space: normal;
		color: #718ea9;
		background-color: #f0f3f6;
	}
	.subtext {
		font-size: 12px;
		margin-top: 6px;
		color: #666;
	}
</style>
<?php if( isset( $errors ) && count( $errors ) ) {
	foreach( $errors as $error ) {
		echo '<p class="notice">Error: ' . $error . '</p>';
	}
}
?>

<?php $this->view($content);