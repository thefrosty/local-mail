<?php
/**
 * Helper class to read localmail.
 */
class Local_Mail {
	
	/** Singleton *************************************************************/
	private static $instance;
	
	private $mail;

	/**
	 * Main Instance
	 *
	 * @staticvar 	array 	$instance
	 * @return 		The one true instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
			self::$instance->init();
		}
		return self::$instance;
	}
	
	/* To infinity and beyond */
	function init() {
		$this->mail = isset( $_GET['mail'] ) ? $_GET['mail'] : '/var/mail/austinpassy';
		$this->session();
	}
	
	/** Session */
	function session() {
		
		if ( session_id() === '' ) {
			session_start();
		}
	}
	
	/** Cleared? */
	function is_cleared() {
		
		if ( isset( $_GET['clear_all'] ) && 'true' === $_GET['clear_all'] ) {
			return true;
		}
		return false;
	}

	/** Redirect */
	function meta_refresh() {
		
		if ( $this->is_cleared() ) {
			$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://' . $_SERVER["SERVER_NAME"] : 'https://' . $_SERVER["SERVER_NAME"];
			$url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":" . $_SERVER["SERVER_PORT"] : "";
			echo '<meta http-equiv="refresh" content="4; url=' . $url . '">';
		}
	}

	/** HTML */
	function output() {
		$fileCleared = false;
		
		// Clear file?
		if ( $this->is_cleared() ) {
			$handle = fopen( $this->mail, "w" );
			fclose( $handle );
			$fileCleared = true;
		}
		
		// Read file
		if ( file_exists( $this->mail ) ) {
			
			//$email = file( $this->logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ); // Array
			$email = file_get_contents( $this->mail ); // String
			
			if ( $fileCleared )
				echo '<p><em>Cleared.</em></p>';
				
			if ( $email ) {
				echo '<p style="border-bottom:1px solid #efefef; padding-bottom:8px">';
				
				if ( is_array( $email) ) {
					echo count( $email ) . ' email(s)';
				}
				else {
					preg_match_all( '/(-{2}[a-z0-9]{11}[.])/i', $email, $matches );
					echo count( $matches[0] ) . ' email(s)';
				}
				
				echo ' [ <strong><a href="?clear_all=true" onclick="return confirm(\'Are you sure?\');">CLEAR ALL</a></strong> ]';
				echo '</p>' . "\n";
				
				echo '<div style="height:100%;overflow:scroll;padding:0;background-color:#faf9f7;border:1px solid #ccc;">' . "\n";
				
//				var_dump( $email ); return;
				if ( is_array( $email ) ) {
					$counter = 0;
					foreach ( $email as $key => $mail ) : $counter++;
						$style = ( $counter % 2 === 0 ) ? 'background-color:#efefef;' : 'background-color:#f9f9f9;';
						$mail  = htmlspecialchars( $mail );
						echo "<pre style='$style'>$mail</pre>\n";
					endforeach;
				}
				else {
					
					$email = htmlspecialchars( $email );
					// MATCH: --2A6C57A7370.
					//echo preg_replace( '/(-{2}[a-z0-9]{11}[.])/i', "</pre>\n\n<pre style='border-top:5px double #939393; padding-top:20px'>\n", $email, -1, $count );
					$array = preg_split( '/(-{2}[a-z0-9]{11}[.])/i', $email );
					
					$counter = 0;
					foreach ( $array as $key => $mail ) : $counter++;
						$style = ( $counter % 2 === 0 ) ? 'background-color:#efefef;' : 'background-color:#f9f9f9;';
						
						echo "<pre style='border-top:1px solid #DEDEDE;padding:10px 20px;$style'>\n";
							echo $mail;
						echo "</pre>\n";
					endforeach;
				}
				
				echo '</div>';
			}
			else {
				echo '<p>#EmailZero!</p>';
			}
		}
		else {
			echo '<p><em>There was a problem reading the file.</em></p>';
			var_dump( $this->mail );
		}
	}
	
};

/**
 * Initiate.
 *
 */
function LOCAL_MAIL() {
	return Local_Mail::instance();
}
LOCAL_MAIL();

?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <?php LOCAL_MAIL()->meta_refresh(); ?>

        <link rel="stylesheet" href="css/normalize.min.css">
        <link rel="stylesheet" href="css/main.css">
    </head>
    <body>

        <div class="main-container">
            <div class="main wrapper clearfix">
                <h3>Local E-Mail</h3>
                <?php LOCAL_MAIL()->output(); ?>
                
                <footer>
                	<p>Built by <a href="http://austin.passy.co">Frosty</a></p>
                </footer>

            </div> <!-- #main -->
        </div> <!-- #main-container -->
    </body>
</html>