<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<!--[if IE]>
<script src="http://explorercanvas.googlecode.com/svn/trunk/excanvas.js"></script>
<![endif]-->
<script>

// Run the code when the DOM is ready
$( pieChart );

function pieChart() {

  // Config settings
  var chartSizePercent = 50;                        // The chart radius relative to the canvas width/height (in percent)
  var sliceBorderWidth = 1;                         // Width (in pixels) of the border around each slice
  var sliceBorderStyle = "#fff";                    // Colour of the border around each slice
  var sliceGradientColour = "#ddd";                 // Colour to use for one end of the chart gradient
  var maxPullOutDistance = 20;                      // How far, in pixels, to pull slices out when clicked
  var pullOutFrameStep = 3;                         // How many pixels to move a slice with each animation frame
  var pullOutFrameInterval = 30;                    // How long (in ms) between each animation frame
  var pullOutLabelPadding = 60;                     // Padding between pulled-out slice and its label  
  var pullOutLabelFont = "bold 12px 'Trebuchet MS', Verdana, sans-serif";  // Pull-out slice label font
  var pullOutValueFont = "bold 10px 'Trebuchet MS', Verdana, sans-serif";  // Pull-out slice value font
  var pullOutValuePrefix = "€";                     // Pull-out slice value prefix
  var pullOutShadowColour = "rgba( 0, 0, 0, .5 )";  // Colour to use for the pull-out slice shadow
  var pullOutShadowOffsetX = 5;                     // X-offset (in pixels) of the pull-out slice shadow
  var pullOutShadowOffsetY = 5;                     // Y-offset (in pixels) of the pull-out slice shadow
  var pullOutShadowBlur = 5;                        // How much to blur the pull-out slice shadow
  var pullOutBorderWidth = 2;                       // Width (in pixels) of the pull-out slice border
  var pullOutBorderStyle = "#333";                  // Colour of the pull-out slice border
  var chartStartAngle = -.5 * Math.PI;              // Start the chart at 12 o'clock instead of 3 o'clock

  // Declare some variables for the chart
  var canvas;                       // The canvas element in the page
  var currentPullOutSlice = -1;     // The slice currently pulled out (-1 = no slice)
  var currentPullOutDistance = 0;   // How many pixels the pulled-out slice is currently pulled out in the animation
  var animationId = 0;              // Tracks the interval ID for the animation created by setInterval()
  var chartData = [];               // Chart data (labels, values, and angles)
  var chartColours = [];            // Chart colours (pulled from the HTML table)
  var totalValue = 0;               // Total of all the values in the chart
  var canvasWidth;                  // Width of the canvas, in pixels
  var canvasHeight;                 // Height of the canvas, in pixels
  var centreX;                      // X-coordinate of centre of the canvas/chart
  var centreY;                      // Y-coordinate of centre of the canvas/chart
  var chartRadius;                  // Radius of the pie chart, in pixels

  // Set things up and draw the chart
  init();


  /**
   * Set up the chart data and colours, as well as the chart and table click handlers,
   * and draw the initial pie chart
   */

  function init() {

    // Get the canvas element in the page
    canvas = document.getElementById('chart');

    // Exit if the browser isn't canvas-capable
    if ( typeof canvas.getContext === 'undefined' ) return;

    // Initialise some properties of the canvas and chart
    canvasWidth = canvas.width;
    canvasHeight = canvas.height;
    centreX = canvasWidth / 2;
    centreY = canvasHeight / 2;
    chartRadius = Math.min( canvasWidth, canvasHeight ) / 2 * ( chartSizePercent / 100 );

    // Grab the data from the table,
    // and assign click handlers to the table data cells
    
    var currentRow = -1;
    var currentCell = 0;

    $('#chartData td').each( function() {
      currentCell++;
      if ( currentCell % 2 != 0 ) {
        currentRow++;
        chartData[currentRow] = [];
        chartData[currentRow]['label'] = $(this).text();
      } else {
       var value = parseFloat($(this).text());
       totalValue += value;
       value = value.toFixed(2);
       chartData[currentRow]['value'] = value;
      }

      // Store the slice index in this cell, and attach a click handler to it
      $(this).data( 'slice', currentRow );
      $(this).click( handleTableClick );

      // Extract and store the cell colour
      if ( rgb = $(this).css('color').match( /rgb\((\d+), (\d+), (\d+)/) ) {
        chartColours[currentRow] = [ rgb[1], rgb[2], rgb[3] ];
      } else if ( hex = $(this).css('color').match(/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/) ) {
        chartColours[currentRow] = [ parseInt(hex[1],16) ,parseInt(hex[2],16), parseInt(hex[3], 16) ];
      } else {
        alert( "Error: Colour could not be determined! Please specify table colours using the format '#xxxxxx'" );
        return;
      }

    } );

    // Now compute and store the start and end angles of each slice in the chart data

    var currentPos = 0; // The current position of the slice in the pie (from 0 to 1)

    for ( var slice in chartData ) {
      chartData[slice]['startAngle'] = 2 * Math.PI * currentPos;
      chartData[slice]['endAngle'] = 2 * Math.PI * ( currentPos + ( chartData[slice]['value'] / totalValue ) );
      currentPos += chartData[slice]['value'] / totalValue;
    }

    // All ready! Now draw the pie chart, and add the click handler to it
    drawChart();
    $('#chart').click ( handleChartClick );
  }


  /**
   * Process mouse clicks in the chart area.
   *
   * If a slice was clicked, toggle it in or out.
   * If the user clicked outside the pie, push any slices back in.
   *
   * @param Event The click event
   */

  function handleChartClick ( clickEvent ) {

    // Get the mouse cursor position at the time of the click, relative to the canvas
    var mouseX = clickEvent.pageX - this.offsetLeft;
    var mouseY = clickEvent.pageY - this.offsetTop;

    // Was the click inside the pie chart?
    var xFromCentre = mouseX - centreX;
    var yFromCentre = mouseY - centreY;
    var distanceFromCentre = Math.sqrt( Math.pow( Math.abs( xFromCentre ), 2 ) + Math.pow( Math.abs( yFromCentre ), 2 ) );

    if ( distanceFromCentre <= chartRadius ) {

      // Yes, the click was inside the chart.
      // Find the slice that was clicked by comparing angles relative to the chart centre.

      var clickAngle = Math.atan2( yFromCentre, xFromCentre ) - chartStartAngle;
      if ( clickAngle < 0 ) clickAngle = 2 * Math.PI + clickAngle;
                  
      for ( var slice in chartData ) {
        if ( clickAngle >= chartData[slice]['startAngle'] && clickAngle <= chartData[slice]['endAngle'] ) {

          // Slice found. Pull it out or push it in, as required.
          toggleSlice ( slice );
          return;
        }
      }
    }

    // User must have clicked outside the pie. Push any pulled-out slice back in.
    pushIn();
  }


  /**
   * Process mouse clicks in the table area.
   *
   * Retrieve the slice number from the jQuery data stored in the
   * clicked table cell, then toggle the slice
   *
   * @param Event The click event
   */

  function handleTableClick ( clickEvent ) {
    var slice = $(this).data('slice');
    toggleSlice ( slice );
  }


  /**
   * Push a slice in or out.
   *
   * If it's already pulled out, push it in. Otherwise, pull it out.
   *
   * @param Number The slice index (between 0 and the number of slices - 1)
   */

  function toggleSlice ( slice ) {
    if ( slice == currentPullOutSlice ) {
      pushIn();
    } else {
      startPullOut ( slice );
    }
  }

 
  /**
   * Start pulling a slice out from the pie.
   *
   * @param Number The slice index (between 0 and the number of slices - 1)
   */

  function startPullOut ( slice ) {

    // Exit if we're already pulling out this slice
    if ( currentPullOutSlice == slice ) return;

    // Record the slice that we're pulling out, clear any previous animation, then start the animation
    currentPullOutSlice = slice;
    currentPullOutDistance = 0;
    clearInterval( animationId );
    animationId = setInterval( function() { animatePullOut( slice ); }, pullOutFrameInterval );

    // Highlight the corresponding row in the key table
    $('#chartData td').removeClass('highlight');
    var labelCell = $('#chartData td:eq(' + (slice*2) + ')');
    var valueCell = $('#chartData td:eq(' + (slice*2+1) + ')');
    labelCell.addClass('highlight');
    valueCell.addClass('highlight');
  }

 
  /**
   * Draw a frame of the pull-out animation.
   *
   * @param Number The index of the slice being pulled out
   */

  function animatePullOut ( slice ) {

    // Pull the slice out some more
    currentPullOutDistance += pullOutFrameStep;

    // If we've pulled it right out, stop animating
    if ( currentPullOutDistance >= maxPullOutDistance ) {
      clearInterval( animationId );
      return;
    }

    // Draw the frame
    drawChart();
  }

 
  /**
   * Push any pulled-out slice back in.
   *
   * Resets the animation variables and redraws the chart.
   * Also un-highlights all rows in the table.
   */

  function pushIn() {
    currentPullOutSlice = -1;
    currentPullOutDistance = 0;
    clearInterval( animationId );
    drawChart();
    $('#chartData td').removeClass('highlight');
  }
 
 
  /**
   * Draw the chart.
   *
   * Loop through each slice of the pie, and draw it.
   */

  function drawChart() {

    // Get a drawing context
    var context = canvas.getContext('2d');
        
    // Clear the canvas, ready for the new frame
    context.clearRect ( 0, 0, canvasWidth, canvasHeight );

    // Draw each slice of the chart, skipping the pull-out slice (if any)
    for ( var slice in chartData ) {
      if ( slice != currentPullOutSlice ) drawSlice( context, slice );
    }

    // If there's a pull-out slice in effect, draw it.
    // (We draw the pull-out slice last so its drop shadow doesn't get painted over.)
    if ( currentPullOutSlice != -1 ) drawSlice( context, currentPullOutSlice );
  }


  /**
   * Draw an individual slice in the chart.
   *
   * @param Context A canvas context to draw on  
   * @param Number The index of the slice to draw
   */

  function drawSlice ( context, slice ) {

    // Compute the adjusted start and end angles for the slice
    var startAngle = chartData[slice]['startAngle']  + chartStartAngle;
    var endAngle = chartData[slice]['endAngle']  + chartStartAngle;
      
    if ( slice == currentPullOutSlice ) {

      // We're pulling (or have pulled) this slice out.
      // Offset it from the pie centre, draw the text label,
      // and add a drop shadow.

      var midAngle = (startAngle + endAngle) / 2;
      var actualPullOutDistance = currentPullOutDistance * easeOut( currentPullOutDistance/maxPullOutDistance, .8 );
      startX = centreX + Math.cos(midAngle) * actualPullOutDistance;
      startY = centreY + Math.sin(midAngle) * actualPullOutDistance;
      context.fillStyle = 'rgb(' + chartColours[slice].join(',') + ')';
      context.textAlign = "center";
      context.font = pullOutLabelFont;
      context.fillText( chartData[slice]['label'], centreX + Math.cos(midAngle) * ( chartRadius + maxPullOutDistance + pullOutLabelPadding ), centreY + Math.sin(midAngle) * ( chartRadius + maxPullOutDistance + pullOutLabelPadding ) );
      context.font = pullOutValueFont;
      context.fillText( chartData[slice]['value'] + pullOutValuePrefix + " (" + ( parseInt( chartData[slice]['value'] / totalValue * 100 + .5 ) ) +  "%)", centreX + Math.cos(midAngle) * ( chartRadius + maxPullOutDistance + pullOutLabelPadding ), centreY + Math.sin(midAngle) * ( chartRadius + maxPullOutDistance + pullOutLabelPadding ) + 20 );
      context.shadowOffsetX = pullOutShadowOffsetX;
      context.shadowOffsetY = pullOutShadowOffsetY;
      context.shadowBlur = pullOutShadowBlur;

    } else {

      // This slice isn't pulled out, so draw it from the pie centre
      startX = centreX;
      startY = centreY;
    }

    // Set up the gradient fill for the slice
    var sliceGradient = context.createLinearGradient( 0, 0, canvasWidth*.75, canvasHeight*.75 );
    sliceGradient.addColorStop( 0, sliceGradientColour );
    sliceGradient.addColorStop( 1, 'rgb(' + chartColours[slice].join(',') + ')' );

    // Draw the slice
    context.beginPath();
    context.moveTo( startX, startY );
    context.arc( startX, startY, chartRadius, startAngle, endAngle, false );
    context.lineTo( startX, startY );
    context.closePath();
    context.fillStyle = sliceGradient;
    context.shadowColor = ( slice == currentPullOutSlice ) ? pullOutShadowColour : "rgba( 0, 0, 0, 0 )";
    context.fill();
    context.shadowColor = "rgba( 0, 0, 0, 0 )";

    // Style the slice border appropriately
    if ( slice == currentPullOutSlice ) {
      context.lineWidth = pullOutBorderWidth;
      context.strokeStyle = pullOutBorderStyle;
    } else {
      context.lineWidth = sliceBorderWidth;
      context.strokeStyle = sliceBorderStyle;
    }

    // Draw the slice border
    context.stroke();
  }


  /**
   * Easing function.
   *
   * A bit hacky but it seems to work! (Note to self: Re-read my school maths books sometime)
   *
   * @param Number The ratio of the current distance travelled to the maximum distance
   * @param Number The power (higher numbers = more gradual easing)
   * @return Number The new ratio
   */

  function easeOut( ratio, power ) {
    return ( Math.pow ( 1 - ratio, power ) + 1 );
  }

};

</script>

<aside style="width: 100%; border: 1px solid black; background: white;"><p style="font-size: 20px; margin-left: 50px;">financement assuré : 528 / 10621€
<progress value="528" max="10621" style="height: 30px; width: 500px; margin-left: 50px;">
  <span style="float: right; margin-right: 50px; background-color: silver; border: 1px solid black; height: 30px; width: 500px; overflow: hidden; position: relative; bottom: 25px;">
    <span style="float: left; background-color: #1c2026; height: 30px; width: 25px;"><!-- diviser le montant par 21,242--></span>
  </span>
</progress></p>
</aside>

<p style="clear: both;"></p>

<h2 style="float: right; width: 250px;">Budget prévisionnel exercice 2012</h2>

  <canvas id="chart" width="730" height="400" style="float: left;"></canvas>

  <table id="chartData">

    <tr>
      <th>objet</th><th>montant (€)</th>
     </tr>

    <tr style="color: #0DA068">
      <td>Films et vidéos</td><td>4029</td>
    </tr>

    <tr style="color: #ED5713">
      <td>JIL</td><td>2423</td>
    </tr>

    <tr style="color: #194E9C">
      <td>Jappix Project</td><td>1983</td>
    </tr>

    <tr style="color: #ED9C13">
      <td>Fonctionnement</td><td>1801</td>
    </tr>

  </table>


<p style="clear: both;"></p>

<section>
<h2>Adhérez !</h2>
<div style="width: 490px; float: right;">
<table style="width: 490px; border: 1px solid navy;">
<tbody style="width: 490px; border: 1px solid navy;">
<tr style="border: 1px solid navy;">
<th style="text-align: center; border: 1px solid navy;">type d’adhésion</th>

<th style="width: 28%; border: 1px solid navy;">tarif chômeurs, étudiants, assimilés</th>
<th style="width: 28%; border: 1px solid navy;">tarif autres</th>
</tr>

<tr style="text-align: center; border: 1px solid navy;">
<td style="text-align: center; border: 1px solid navy;">membre actif</td>
<td style="text-align: center; border: 1px solid navy;">10€</td>
<td style="text-align: center; border: 1px solid navy;">18€</td>
</tr>
<tr>
<td style="text-align: center; border: 1px solid navy;">membre bienfaiteur</td>
<td style="text-align: center; border: 1px solid navy;">20€</td>

<td style="text-align: center; border: 1px solid navy;">36€</td>
</tr>

<tr style="text-align: center; background-color: white; border: 1px solid navy;">
<td style="text-align: center; border: 1px solid navy;" title="recevez gratuitement un Tshirt!">membre d’honneur</td>
<td style="text-align: center; border: 1px solid navy;" title="recevez gratuitement un Tshirt!">30€</td>
<td style="text-align: center; border: 1px solid navy;" title="recevez gratuitement un Tshirt!">54€</td>
</tr>
</tbody>
</table>

<p style="height: 20px;"></p>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
  <input name="cmd" value="_s-xclick" type="hidden">
  <input name="hosted_button_id" value="VGA2GSZNZQ9HJ" type="hidden">
  <input name="on0" value="" type="hidden">
  <div class="clearfix">
<input type="hidden" name="on1" value="Nom, prénom">Nom, prénom  <input type="text" name="os1" maxlength="200" class="input"><br />
<p style="height:5px;"></p>
<input type="hidden" name="on0" value="Type d'adhésion">Type d'adhésion   <select name="os0" class="input">
	<option value="Membre actif">Membre actif €18,00</option>
	<option value="Membre actif (réduit)">Membre actif (réduit) €10,00</option>
	<option value="Membre bienfaiteur">Membre bienfaiteur €36,00</option>
	<option value="Membre bienfaiteur (réduit)">Membre bienfaiteur (réduit) €20,00</option>
	<option value="Membre d'honneur">Membre d'honneur €54,00</option>
	<option value="Membre d'honneur (réduit)">Membre d'honneur (réduit) €30,00</option>
      </select>

    </div>
  <input name="currency_code" value="EUR" type="hidden">
<p style="height: 2px;"></p>
  <div class="actions">
    <input class="btn success input" value="Envoyer sa cotisation" type="submit">
  </div>
</form>
</div>
<p style="width: 490px; ">Comparé au nombre de personnes touchées par les actions de PostPro, le nombre d’adhérents à l’association n’est actuellement pas suffisant pour qu’elle soit reconnue auprès des collectivités et d’éventuels partenaires. Ainsi, un nombre de 100 adhésions s’impose. Afin de manifester votre soutien au projet Jappix, nous vous invitons à adhérer à PostPro.</p>

<p style="width: 490px;">Pour ceci, rien de plus simple. Renseignez tout simplement les informations sur <a href="http://membres.post-pro.fr/adhesion/">l’interface d’adhésions</a> et envoyez votre cotisation annuelle au trésorier par chèque à l’ordre de PostPro :</p>
<q>Armand Germain<br>trésorier de PostPro<br>100 impasse des claux<br>Le Broussan<br>83330 Évenos</q>
<p>Vous pouvez aussi payer en ligne via le formulaire ci-contre.</p>
<p style="clear: both;"></p>
</section>

<section>
<h2>Soutenez nos actions par un don !</h2>
<img src="http://www.post-pro.fr/campagnes/tshirt.png" width="490" style="display: block;float: right; border: 1px solid black">
<p style="width: 490px; ">Parce que PostPro a besoin de moyens financiers pour poursuivre ses projets, faire un don est la manière la plus facile de l’aider. Grâce à la déduction fiscale de 66% pour les particuliers, vous ne paierez plus que 34% du montant de votre don. Ainsi, en donnant 100€, vous ne paierez que 34€ !</p>
<p style="width: 490px; ">Ainsi, lorsque 20 personnes auront fait un don de 100€ ou plus, il leur sera envoyé gratuitement un Tshirt PostPro ou Jappix (au choix du donneur).</p>
<p>Différents moyens existent pour faire un don. Vous pouvez envoyer un chèque à l’ordre de <em>PostPro</em> au trésorier, envoyer un virement bancaire (contactez nous avant), ou payer via le formulaire ci-dessous.</p>

 <form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="width: 300px;">
      <div class="clearfix">
        <label for="amount">Montant (€)</label>

        <div class="input">
          <input class="xlarge span2" name="amount" value="25.00" type="input">
        </div>
      </div>
      <div class="clearfix">
        <div class="input">
          <input name="cmd" value="_xclick" type="hidden">
          <input name="business" value="contact@post-pro.fr" type="hidden">
          <input name="item_name" value="Donation to Jappix" type="hidden">

          <input name="currency_code" value="EUR" type="hidden">
        </div>
      </div>

<p style="height: 5px;"></p>

    <div class="actions">
      <input class="btn success" value="Faire un don" type="submit">
    </div>
  </form>
<p style="clear: both; height: 20px;"></p>
</section>

<style>
.wideBox {
display: none;
}

#chart, #chartData {
  border: 1px solid #333;
  background: #fff;
}

#chart {
  display: block;
  margin: 0 ;
  float: left;
  cursor: pointer;
  position: relative;
  bottom: 70px;
}

#chartData {
  width: 250px;
  margin: 0;
  float: right;
  border-collapse: collapse;
}

#chartData th, #chartData td {
  padding: 0.5em;
  border: 1px dotted #666;
  text-align: left;
}

#chartData th {
  border-bottom: 2px solid #333;
  text-transform: uppercase;
}

#chartData td {
  cursor: pointer;
}

#chartData td.highlight {
  background: #e8e8e8;
}

#chartData tr:hover td {
  background: #f0f0f0;
}
</style>
