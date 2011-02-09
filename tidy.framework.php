<?php
	/**
	* @package   Tidy Framework
	* @version   0.1 alpha
	* @author    Spyros Livathinos
	* @copyright Copyright (C) 2011 Spyros Livathinos. All rights reserved.
	* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
	* 
	* ------------------------------------------------------
	* LOCAL				SHARED				REMOTE	
	* ------------------------------------------------------       
	* genLocalCSS()		packBuilder()		genRemoteCSS()
	* genLocalJS()		parseCSS()			genRemoteJS()
	* genLocalHTML()	parseJS()			genRemoteHTML()
	* _localHeader()	doctype()			_remoteHeader()
	*					_header()
	*					_generateOfflince()
	* 					_sysmsg()
	*
	*/ 


	defined('_JEXEC') or die( 'Restricted access' );
	jimport('joomla.filesystem.folder');
	jimport('joomla.filesystem.file');
	
	/**
	* Main class of the Pack Framework. Contains a two tier developing system
	* for local and remote development and deployment of a Joomla 1.6 project.
	*/
	class packBuilder{
		var $container	= null;
		
		/**
		 * Initializes important variables, creates the application instance
		 * used throughout the framework.
		 * @param string $container
		 * @param string $page
		 */
		function packBuilder($container = null, $page = ""){
			$mainframe = JFactory::getApplication();
			
			if(!empty($container)){
				$this->container		= $container;
				$this->doc		= &JFactory::getDocument();
				$this->menu		= &JSite::getMenu();
				$this->page = (!empty($page)) ? $page : "";
				$this->container->typo = "typography";
				$this->container->sysmsg = 0;
				// Set the Joomla generator tag to null
				$this->doc->setGenerator(null);
				
				// Initialize the container parameters
				$browser							= new JBrowser;
				$this->container->systemlibs		= $this->container->baseurl . "/media/system/js";
				$this->container->tempurl 			= $this->container->baseurl . "/templates/" . $this->container->template;
				$this->container->tempurlabs		= JURI::root() . "templates/" . $this->container->template;
				$this->container->path				= JPATH_THEMES.DS.$this->container->template;
				$this->container->browsername		= (strtolower($browser->getBrowser()) == "internet explorer") ? "ie" : strtolower($browser->getBrowser());
				$this->container->browserver		= intval($browser->getVersion());
				$this->container->browserver_long	= intval(str_replace(".","",$browser->getVersion()));
				$this->container->browserver_med	= intval(str_replace(".","",floatval($browser->getVersion())));
				$this->container->bowserplatform	= strtolower($browser->getPlatform());
				$this->container->browseraol		= "";
				$this->container->browseraolver		= "";
				$this->container->browsermobile		= $browser->isMobile();
				$this->container->browserobot		= $browser->isRobot();
				$this->container->browseragent		= strtolower($browser->getAgentString());
				$this->container->browsercss		= $this->container->browsername.$this->container->browserver.".css";
				$this->container->iemsg				= " ";
				
			}
		}
		
		/**
		* Parses external CSS files given a URL string.
		* @param string $url
		* @return string $cssurl
		*/
		function parseCSS($url) {		
			$cssurl = '<link rel="stylesheet" href="'.$url.'" type="text/css" />'."\n";
			return $cssurl;
		}
		
		/**
		* Parses external Javascript files given a URL string.
		* @param string $url
		* @return string $jsurl
		*/
		function parseJS($url){
			$jsurl = '<script type="text/javascript" src="'.$url.'"></script>'."\n";
			return $jsurl;
		}
		
		/**
		* Parse Javascript Declaration
		*/
		function parseJSDeclaration($declaration){
			$script	= '
			<script type="text/javascript">
				' . $declaration . '
			</script>'."\n";
			return $script;
		}
		

		/**
		* Returns a valid DOCTYPE.
		* @param none
		* @return string $html
		*/
		function doctype(){
			$html = "";
			$html .= '<!DOCTYPE html>';
			return $html;
		}
		
		/**
		* Generates a list of CSS file inclusions and appends them to the returned
		* variable. Applies to the local development environment.
		* @param none
		* @return string $html
		*/
		function genLocalCSS(){
			$html	  = "";
			
			// Include the reset stylesheet.
			$this->doc->addStyleSheet($this->container->tempurl . "/css/reset.css", "text/css");
			
			// Include the grid stylesheet (YUI library).
			$grid	  = $this->container->path.DS."css".DS."grid.css";
			if(JFile::exists($grid)){ $this->doc->addStyleSheet($this->container->tempurl . "/css/grid.css", "text/css"); }
	
			// Include the typography stylesheet.
			if($this->container->typo != -1){ $this->doc->addStyleSheet($this->container->tempurl . "/css/" . $this->container->typo . ".css", "text/css"); }
		
			// Include the system message stylesheets.
			switch ($this->container->sysmsg){
				case 1:
					$html .= $this->parseCSS($this->container->tempurl . "/css/messagefly.css");
					break;
				
				case 2:
					$html .= $this->parseCSS($this->container->tempurl . "/css/messagefb.css");
					break;
			}
			
			// Include the main template stylesheet
			$skincss	 = $this->container->path.DS."css".DS."template.css";
			$html		.= (JFile::exists($skincss)) ? $this->parseCSS($this->container->tempurl . "/css/template.css") : "";
			
			// Inlcude the component stylesheet
			if($this->page == "component"){
				$skincomcss	 = $this->container->path.DS."css".DS."component.css";
				$html 		.= (JFile::exists($skincomcss)) ? $this->parseCSS($this->container->tempurl . "/css/component.css") : $this->parseCSS($this->container->tempurl . "/css/component.css");
			}
			
			// Include the error stylesheet
			if($this->page == "error"){
				$skincomcss	 = $this->container->path.DS."css".DS."error.css";
				$html 		.= (JFile::exists($skincomcss)) ? $this->parseCSS($this->container->tempurl . "/css/error.css") : $this->parseCSS($this->container->tempurl . "/css/error.css");
			}
			
			// Include the page offlince stylesheet
			if($this->page == "offline"){
				$skincomcss	 = $this->container->path.DS."css".DS."offline.css";
				$html 		.= (JFile::exists($skincomcss)) ? $this->parseCSS($this->container->tempurl . "/css/offline.css") : $this->parseCSS($this->container->tempurl . "/css/offline.css");
			}
			
			// Include a browser specific override stylesheet
			$browsercss	 = $this->container->path.DS."css".DS.$this->container->browsercss;
			$html		.= (JFile::exists($browsercss)) ? $this->parseCSS($this->container->tempurl . "/css/" . $this->container->browsercss) : "";

			return $html;
		}
		
		
		/**
		* Generates a list of CSS files and appends their contents to a single
		* stylesheet. Applies to the remote development environment.
		* @param none
		* @return string $html
		*/
		function genRemoteCSS(){
			$html	  = "";
			
			$filemtime = filemtime(dirname(__FILE__) . "/../css/pack.min.css");
			// Include the reset, grid, typography, system message, template, component, error
			// and browser specific stylesheets.
			$this->doc->addStyleSheet($this->container->tempurl . "/css/pack.min.css?" . $filemtime, "text/css");
			return $html;
		}
		
		/**
		* Generates a list of Javascript file inclusions for the local and remote
		* environments.
		* @param none
		* @return string $html
		*/
		function genJS(){
			
			$html = '';
			
			if($_SERVER['SERVER_NAME'] == 'localhost')
				$html .= $this->genLocalJS();
			else
				$html .= $this->genRemoteJS();
				
			echo $html;
		}
			
		/**
		* Generates a list of Javascript file inclusions and appends them to the returned 
		* variable. Applies to the local development environment.
		* @param none
		* @return string $html
		* @todo Remove mootools library
		*/
		function genLocalJS(){
			$mainframe = JFactory::getApplication();
			
			$html	 = "";
			
			// Parse Mootools library
			$html .= $this->parseJS($this->container->baseurl . '/media/system/js/mootools-core.js');
			$html .= $this->parseJS($this->container->baseurl . '/media/system/js/mootools-more.js');
			
			// Parse jQuery library
			$html .= $this->parseJS("//ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js");
			//<script>!window.jQuery && document.write(unescape('%3Cscript src="http://exsrv.cti.gr/templates/excellence/js/libs/jquery-1.4.2.js"%3E%3C/script%3E'))</script>

			
			// $html .= $this->parseJS($this->container->tempurl . "/js/libs/cufon.js");
			// $html .= $this->parseJS($this->container->tempurl . "/js/libs/bebas_400.font.js");
			$frscript	= "";
			$opt		= "";

			$html .= $this->parseJS($this->container->tempurl . "/js/libs/sysmsgfly.js");

			/*load all window.onload js*/
			$script		 = 'function loadall(){ ';
			$script		.= ($this->container->iemsg > 0 && $this->container->browsername == "ie" && ($this->container->iemsg == 1 || $this->container->iemsg == 3)) ? "popupfly('rumiiewarning');" : "";
			if($this->container->sysmsg == 1){ $script	.= ($this->container->sysmsg == 1) ? "sysMsgFly();" : "sysMsgFb();"; }
			$script		.= ' }; window.onload=function(){ loadall(); };';
			$html		.= $this->parseJSDeclaration($script);
			
			$html .= $this->parseJS($this->container->tempurl . "/js/script.js");
			
			return $html;
		}
		
		/**
		* Generates a list of Javascript file inclusions and appends them to the returned 
		* variable. Applies to the remote environment.
		* @param none
		* @return string $html
		*/
		function genRemoteJS(){
			$mainframe = JFactory::getApplication();
		
			$html  = "";
			
			$filemtime = filemtime(dirname(__FILE__) . "/../js/plugins.min.js");
			$html .= $this->parseJS($this->container->tempurl . "/js/plugins.min.js?" . $filemtime);
			
			$filemtime = filemtime(dirname(__FILE__) . "/../js/script.min.js");
			$html .= $this->parseJS($this->container->tempurl . "/js/script.min.js?" . $filemtime);
			
			return $html;
		}
		
		/**
		* Generates a list of inclusion directives for the local or remote development
		* environment.
		* @param none
		* @return string The HTML code generated for local or remote deployment
		*/
		function genHeader(){
			if($this->container->iemsg > 0 && $this->container->browsername == "ie"){
				$msg = "<div id='rumiiewarning'><div class='inner'><div class='title'>" . $this->container->warningtitle . "</div><div class='content'>" . $this->container->iewarningmsg . "</div></div></div>";
				
				/*load ie warning css*/
				$iediscss	= $this->container->path.DS."css".DS."iewarning.css";
				$css = (JFile::exists($iediscss)) ? $this->parseCSS($this->container->tempurl."/css/iewarning.css") : $this->parseCSS($this->container->tempurl . "/css/iewarning.css");
				
				switch ($this->container->iemsg){
					case 1: /*IE6 only (Warning only)*/
						if($this->container->browserver <= 6){
							$this->_generate($css . "</head><body id=\"rumibody\" class=\"rumibody\">" . $msg);
						}else{
							$this->_generate();
						}
						break;
					
					case 2: /*IE6 only (Warning and Disabled website)*/
						if($this->container->browserver <= 6){
							$this->_generate($css . "</head><body id=\"rumibody\" class=\"rumibody\">" . $msg . "</body></html>", true);
						}else{
							$this->_generate();
						}
						break;
					
					case 3: /*All IE (Warning only)*/
						$this->_generate($css . "</head><body id=\"rumibody\" class=\"rumibody\">" . $msg);
						break;
					
					case 4: /*All IE (Warning and Disabled website)*/
						$this->_generate($css . "</head><body id=\"rumibody\" class=\"rumibody\">" . $msg . "</body></html>", true);
						break;
				}
			}else{
				$this->_generate();
			}
		}
		
		/**
		* Constructs the header of the local development index page, including 
		* Javascript and CSS files.
		* @param none
		* @return string $html
		*/
		function _localHeader(){
			
			$document =&JFactory::getDocument();
			
			JHtml::_('behavior.mootools');
			$html	 = "";
			
		 	$html 	.= '<jdoc:include type="head" />';
			
			// Unset all the mootools libraries fetched by Joomla
			unset($document->_scripts[$this->container->baseurl . '/media/system/js/core.js']);
			unset($document->_scripts[$this->container->baseurl . '/media/system/js/mootools-core.js']);
			unset($document->_scripts[$this->container->baseurl . '/media/system/js/mootools-more.js']);
			unset($document->_scripts[$this->container->baseurl . '/media/system/js/caption.js']);
			
			$html	.= $this->genLocalCSS();
			return $html;
		}
		
		/**
		* Constructs the header of the local development index page, including 
		* Javascript and CSS files.
		* @param none
		* @return string $html
		*/
		function _remoteHeader(){
			
			$document =&JFactory::getDocument();
			
			JHtml::_('behavior.mootools');
			$html	 = "";
			$html	.= "<jdoc:include type=\"head\" />";
			
			// Unset all the mootools libraries fetched by Joomla
			unset($document->_scripts[$this->container->baseurl . '/media/system/js/core.js']);
			unset($document->_scripts[$this->container->baseurl . '/media/system/js/mootools-core.js']);
			unset($document->_scripts[$this->container->baseurl . '/media/system/js/mootools-more.js']);
			unset($document->_scripts[$this->container->baseurl . '/media/system/js/caption.js']);
			
			$html	.= $this->genRemoteCSS();
			return $html;
		}
		
		
		/**
		* Generates HTML offline page inheritance.
		* @param none
		* @return string $html
		*/
		function _generateOffline(){
			$mainframe = JFactory::getApplication();
			
			$html	 = $this->container->branding;
			$html	.= '<div class="offlinemsg">';
			$html	.= ($this->container->offlinemsg) ? $this->container->offlinemsg :$mainframe->getCfg('offline_message');
			$html	.= '</div>';
			
			if($this->container->loginform == 1){
				if(JPluginHelper::isEnabled('authentication', 'openid')){ JHtml::_('script', 'openid.js'); }
				
				$html .= '
					<div id="formcontainer">
						<script language="javascript" type="text/javascript">
							window.addEvent("domready", function(){ document.formlogin.username.focus(); });
						</script>
						<form action="index.php" method="post" name="formlogin" id="formlogin">
							<div class="forminner">
								<p id="form-login-username">
									<label class="label username" for="username">' . JText::_('Username') . '</label>
									<input name="username" id="username" type="text" class="inputbox" alt="' . JText::_('Username') . '" size="18" />
								</p>
								
								<p id="form-login-password">
									<label class="label passwd" for="passwd">' . JText::_('Password') . '</label>
									<input type="password" name="passwd" class="inputbox" size="18" alt="' . JText::_('Password') . '" id="passwd" />
								</p>
								
								<div class="bottom">
									<p id="form-login-remember">
										<input class="checkbox" type="checkbox" name="remember" value="yes" alt="' . JText::_('Remember me') . '" id="remember" />
										<label class="label remember" for="remember">' . JText::_('Remember me') . '</label>
									</p>
									<p id="form-login-button"><input type="submit" name="Submit" class="button" value="' . JText::_('LOGIN') . '" /></p>
									<div class="clear"></div>
								</div>
								
								<input type="hidden" name="option" value="com_user" />
								<input type="hidden" name="task" value="login" />
								<input type="hidden" name="return" value="' . base64_encode(JURI::base()) . '" />
								' . JHtml::_( "form.token" ) . '
							</div>
						</form>
					</div>
				';
			}
			
			return $html;
		}
		
		/**
		* Generate HTML inheritance.
		* @param string $ie
		* @param boolean $halted
		* @return none
		*/
		function _generate($ie = "", $halted = false){
			
			// Check if we're developing locally or remotely
			// and make sure to call the right header generator.
			if($_SERVER['SERVER_NAME'] == 'localhost')
				$header = $this->_localHeader();
			else
				$header = $this->_remoteHeader();
			
			if($halted == false){
				
				/*load skin file*/
				switch ($this->page){
					case "component":
						$body	= ($ie == "") ? "" : $ie;
						echo $header;
						echo $body;
						echo $this->sysmsg();						/*system message*/
						echo "<jdoc:include type=\"component\" />";
						break;
					
					case "offline":
						$body	= ($ie == "") ? "</head><body id=\"rumibody\" class=\"rumioffline\">" : $ie;
						echo $header;
						echo $body;
						echo $this->paramsbox($this->container->skin);		/*debug and development tools*/
						echo $this->sysmsg();						/*system message*/
						echo "<div id='rumioffline'>";
						echo $this->_generateOffline();
						break;
					
					default:
						$body	= ($ie == "") ? "" : $ie;
						echo $header;
						echo $body;
						echo $this->sysmsg();						/*system message*/
						$skinfile	= $this->container->path.DS."index.php";
						if(JFile::exists($skinfile)){
							require_once $this->container->path.DS."index.php";
						}else{
							die("There are no skin named <strong>" . $this->container->skin . "</strong>");
						}
						break;
				}
				
			}else{
				$body	= ($ie == "") ? "</head><body id=\"rumibody\" class=\"rumibody\">" : $ie;
				$this->doc->addScriptDeclaration('window.onload=function(){ makecenter("rumiiewarning"); };', "text/javascript");
				echo $header . $body;
			}
		}
		
		/**
		* Generates the Joomla system message anchor inclusion code.
		* @param none
		* @return string $html
		*/
		function sysmsg(){
			$html = '<jdoc:include type="message" />';
			return $html;
		}

	}
?>