  <meta charset=utf-8>
<title><?php echo ucfirst($page); ?> &raquo; jappix social cloud</title>
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" href="/css/main.css" type="text/css" media="all" />
<script type="text/javascript" src="http://jappix.org/js/jquery-1.7.js"></script>  

<!-- BEGIN Jappix Mini -->
<script type="text/javascript" src="https://static.jappix.com/php/get.php?l=en&amp;t=js&amp;g=mini.xml"></script>

<script type="text/javascript">
   jQuery(document).ready(function() {
      MINI_GROUPCHATS = ["jappix@conference.codingteam.net"];
      MINI_ANIMATE = true;
      launchMini(false, true, "anonymous.jappix.com");
   });
</script>
<!-- END Jappix Mini -->

<!-- menus -->

<script type="text/javascript">
$(document).ready( function () {

    // cacher les sections
    $("div.deroulant").hide();    
    // afficher le h2
    $("section.derouler h2").each( function () {
        var Texteh2 = $(this).text();
        $(this).replaceWith('<h2 style="cursor:pointer;">' + Texteh2 + '<\/h2>') ;
    } );
    // ouvrir section
    $("section.derouler > h2").click( function () {
        // fermer section :
        if ($(this).next("div.deroulant:visible").length != 0) {
            $(this).next("div.deroulant").slideUp("normal");
        } 
        // cacher section avant d’afficher :
        else {
            $("div.deroulant").slideUp("normal");
            $(this).next("div.deroulant").slideDown("normal");
        }
        // pas suivre liens
        return false;
    });  

} ) ;
</script>

