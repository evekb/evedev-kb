{if $previous_install}
    <br/><div class="block-header2"><img src="{$previous_image}" border="0" alt=""> The EVE Development Killboard is already installed!</div>
    The EVE Development Killboard is already installed. You can either update your board or erase all kills and reinstall.<br/>
	<p><a href="{$update}">Update --&gt;</a></p>
	<p><a href="?step=2&erase=1">Permanently erase all kills --&gt;</a></p>

    <p>If you do not wish to run the installation then remove the /install directory from your EVE Development Killboard installation to ensure it isn't run again.
    </p>
{/if}

<div class="block-header2"><img src="{$php_image}" border="0" alt=""> PHP Version &amp; Extensions</div>
{if $php_ok}
Your version of PHP is OK.
{else}
Your version of PHP does not meet the minimum requirements. You must be running at least PHP5. Ask your host to fix this.
{/if}
<br />
{if $mysqli_ok}
You have the MySQLi extension installed and working.
{else}
You don't appear to have the MySQLi extension installed. You need to ask your host to install it.
{/if}

<br /><br /><div class="block-header2"><img src="{$gd_image}" border="0" alt=""> Graphics</div>
{if $gd_exists}<b>GD is available.</b><br/>
    {if !$gd_truecolour}Your GD is outdated and will cause problems, please contact your system administrator to upgrade to GD 2.0 or higher.<br/>
    {/if}
{else}
    <b>GD is NOT available.</b><br/>The Killboard will be unable to output character portraits, corporation logos or signatures. Please speak with your system administrator to install GD 2.0 or higher.<br/>
    <br/>
    You can continue with the installation, but the Killboard might not run correctly.<br/>
{/if}
Now let's see if you've got the FreeType library needed for painting TrueType (.TTF) fonts onto images:<br/>
{if $dg_ttf} I found FreeType support. It is needed by the signature mod. Good!<br/>
{else} Unfortunately, I was unable to locate FreeType support so you can't use all available signatures. Aww :(<br/>
{/if}

<br/><div class="block-header2"><img src="{$dir_image}" border="0" alt=""> Directory structure</div>
{if $dir_writable}
    Cache directory is writeable, testing for subdirectories now:<br/>
    {$dir_text}
{else}
    I cannot write into ../cache, you need to fix that for me before you can continue.<br/>
    Please issue a "chmod 777 ../cache" and "chmod 777 ../cache/*" on the commandline inside of this directory.<br/>
{/if}

<br/><div class="block-header2"><img src="{$conf_image}" border="0" alt=""> Config</div>
{if !$conf_exists}Please create the file "kbconfig.php" and make it writeable by the webserver.<br/>
{elseif $conf_conditional}
    I cannot write into "../kbconfig.php", you need to fix that for me before you can continue.<br/>
    Please issue a "chmod 777 ../kbconfig" on the commandline inside of this directory<br/>
{else}The config file "../kbconfig.php" is there and writeable, excellent!<br/>
{/if}

<br/><div class="block-header2"><img src="{$conn_image}" border="0" alt=""> Connectivity</div>
I will now test if your web server allows remote connections. This is needed for anything relating to the API, EVE-Central syncing, and using CCPs portrait generator.<br/>
<br/>
<b>Let's start with fopen:</b><br/>
{if $conn_fopen_exists}
    allow_url_fopen is enabled, I will try to fetch the test image: '{$conn_url}'<br/>
    {if $conn_fopen_success}
	Seems to be ok, I got the file.<br/>
    {else}
	I couldn't get the file. This might be a firewall related issue or the eve-dev server is not available.<br/>
    {/if}
{else}
    allow_url_fopen is disabled, nevertheless I will try a socket connect now.<br/>
    {if $conn_http_success}
	Seems to be ok, I got the file.<br/>
    {else}
	I couldn't get the file. This might be a firewall related issue or the eve-dev server is not available.<br/>
    {/if}
{/if}
<br/>
<b>Let's try cURL now; it's used for some sections of the killboard but if it's absent we fall back to fopen:</b><br/>
{if $conn_curl_exists}
    cURL seems to be available, I will try to fetch the test image: '{$conn_url}'<br/>
    {if $conn_curl_success}
	Seems to be ok, I got the file.<br/>
    {else}
	I couldn't get the file. This might be a firewall related issue or the eve-dev server is not available.<br/>
    {/if}
{/if}

{if !$stoppage}
<p><a href="?step={$nextstep}">Next Step --&gt;</a></p>
{/if}