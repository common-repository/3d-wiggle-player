jQuery(document).ready(function () {
    jQuery("[data-psswiggleplayer-autoplay=1]").addClass("hover");
    jQuery("[data-psswiggleplayer-autoplay]").hover(function () {
        var t = jQuery(this);
        if (!t.hasClass("hover")) {
            t.addClass("hover");
        }
    }, function () {
        var t = jQuery(this);
        if (t.attr("data-psswiggleplayer-autostop") != "0") {
            t.removeClass("hover");
        }
    });
    jQuery("[data-psswiggleplayer-dialog=1]").on('click', function (event) {
        var img = jQuery(this);
        new PSSWiggleDialog(img.closest('div').eq(0).attr("id"), img.attr("data-psswiggleplayer-fullsrc"), img.attr("data-psswiggleplayer-fullwidth") / 2, img.attr("data-psswiggleplayer-fullheight"));
        return false;
    });
});

function PSSWiggleDialog(animationClass, src, width, height) {
    this.state = 'loading';
    this.viewerContainerVerticalPadding = 40;
    this.viewerContainerHoriontalPadding = 15;
    this.viewerMinSize = 200;

    this.imageWidth = width;
    this.imageHeight = height;

    this.domMain = jQuery("<div/>")
        .attr("id", "psswigglefullscreen");

    jQuery("<div/>")
        .attr("id", "background")
        .on('click', this.close.bind(this))
        .appendTo(this.domMain);

    this.viewerContainer = jQuery("<div/>").attr("id", "viewer_container")
        .on('click', this.close.bind(this))
        .appendTo(this.domMain);

    this.viewer = jQuery("<div/>")
        .attr("id", "viewer")
        .css("background-image", "url(\"" + src + "\")")
        .css("background-position", "0")
        .css("background-size", (this.imageWidth * 2) + "px " + this.imageHeight + "px")
        .css("width", width + "px")
        .css("height", height + "px")
        .appendTo(this.viewerContainer);

    this.viewerImage = jQuery("<img>")
        .addClass('viewer')
        .addClass(animationClass)
        .on('load', this.viewerimageonload.bind(this))
        .on('error', this.viewerimageonerror.bind(this))
        .appendTo(this.viewer);

    this.controls = jQuery("<div/>")
        .attr("id", "controls")
        .appendTo(this.viewerContainer);

    jQuery('<a href="#" id="close"><img alt="Close" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAATKgAAEyoBa29npgAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAJASURBVFiF1Zi/ihpRFId/mwXbxWaKqGEeYArL2FilmkaQ6fZB0g6OIinHF9hnsAkWWYRYbLoR1mLrQdMGxlLkS+NdBlfHUVZyc+HX3Ms95zvn/r83gM4sVUm+pC+SPkmqSfq4a/staSUplfRD0ndJf86yDpRVF5gCG8qXza5Pt6yfmxIZakv6JumzqZjP5xqPx1osFlqtVloul5Kker2uWq0mz/PU6XTUbDbzdn5J+irp56UZugVGJtT1ek0URbiui6RScl2XKIpYr9f5rI12tg/6PQZzB0wAttstcRzjOE5pkH05jkMcx2y3WwM12fkoBXQHPANkWYbv+xeD7Mv3fbIsM1DPh6AODdMEIE1TPM97Nxgjz/NI0zSfqdsioJHJzDVg8lC5TI2OAbXNnHnPYSoavtycah8CegKI4/jqMEZxHBugp32grlnaRaupUqmc7bSoj+M4+S2hmweaAkRRVGh4NpsRhmFpmDAMmc1mhVBRFBmgqQGqsjsOTm16YRgC0Ov1TsL0ej2AkwG4rmuANkBVwD1AkiSlojaOirJpoi4DLokkSQzUvYCHMpEcctjv99+09fv9k8DHMg88CHgECILgrMlqHA8Gg9e6wWBwFLRIQRAYoEcBLwCtVussI3mA4XDIcDh8A1hWrVbLAL0IyAAajcbZhiS9ghiwS2w0Gg1jIvsg24qNQ2bdpLZu2Vu3MVp3dFh3uFp5/bDugmblFda6S76VzyDrHopWPqX/6WfDf/Udsy9rPqz2y1W/9P4CS5G1T3SkXSMAAAAASUVORK5CYII=" /></a>')
        .on('click', this.close.bind(this))
        .appendTo(this.controls);

    this.messageImage = jQuery("<img>")
        .addClass('message')
        .attr("src", 'data:image/gif;base64,R0lGODlhKwALAPEAAP///2dnZ7S0tGdnZyH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAKwALAAACMoSOCMuW2diD88UKG95W88uF4DaGWFmhZid93pq+pwxnLUnXh8ou+sSz+T64oCAyTBUAACH5BAkKAAAALAAAAAArAAsAAAI9xI4IyyAPYWOxmoTHrHzzmGHe94xkmJifyqFKQ0pwLLgHa82xrekkDrIBZRQab1jyfY7KTtPimixiUsevAAAh+QQJCgAAACwAAAAAKwALAAACPYSOCMswD2FjqZpqW9xv4g8KE7d54XmMpNSgqLoOpgvC60xjNonnyc7p+VKamKw1zDCMR8rp8pksYlKorgAAIfkECQoAAAAsAAAAACsACwAAAkCEjgjLltnYmJS6Bxt+sfq5ZUyoNJ9HHlEqdCfFrqn7DrE2m7Wdj/2y45FkQ13t5itKdshFExC8YCLOEBX6AhQAADsAAAAAAAAAAAA=')
        .appendTo(this.viewer);

    this.calculatePositions();

    jQuery(document.body)
        .on('keydown', this.onkeydown.bind(this))
        .append(this.domMain);

    jQuery(window).on('resize', this.onresize.bind(this));

    this.viewerImage
        .attr("src", src);
}

PSSWiggleDialog.prototype.viewerimageonload = function () {
    this.state = 'ready';
    this.calculatePositions();
}

PSSWiggleDialog.prototype.viewerimageonerror = function () {
    this.state = 'error';
    this.calculatePositions();
}

PSSWiggleDialog.prototype.close = function () {
    jQuery(window).off('resize');
    this.domMain.remove();
    return false;
}

PSSWiggleDialog.prototype.onkeydown = function (event) {
    if (event.keyCode == 27) {
        this.close();
    } else if (event.keyCode >= 37 && event.keyCode <= 40) {
        return false;
    }
}
PSSWiggleDialog.prototype.onresize = function () {
    this.calculatePositions();
}

PSSWiggleDialog.prototype.calculatePositions = function () {
    var w = jQuery(window).width();
    var h = jQuery(window).height();

    var maxScale = 0.9;

    // available for display
    var maxWidth = Math.round(w * maxScale) - 2 * this.viewerContainerHoriontalPadding;
    var maxHeight = Math.round(h * maxScale) - 2 * this.viewerContainerVerticalPadding;

    // scale the content in required
    var scale = Math.min(maxWidth / this.imageWidth, maxHeight / this.imageHeight);
    // don't upscale
    scale = Math.min(1, scale);
    // don't scale below min size
    scale = Math.max(scale, this.viewerMinSize / this.imageWidth);

    var scaledWidth = Math.ceil(this.imageWidth * scale);
    var scaledHeight = Math.ceil(this.imageHeight * scale);
    var scaledLeft = Math.floor((w - scaledWidth) / 2 - this.viewerContainerHoriontalPadding);
    var scaledTop = Math.floor((h - scaledHeight) / 2 - this.viewerContainerVerticalPadding);

    this.viewerContainer
        .css("width", (scaledWidth + 2 * this.viewerContainerHoriontalPadding) + "px")
        .css("height", (scaledHeight + 2 * this.viewerContainerVerticalPadding) + "px")
        .css("position", "fixed")
        .css("left", scaledLeft + "px")
        .css("top", scaledTop + "px");

    if (this.state == 'loading') {
        this.messageImage
            .css("visibility", "visible")
            .css("left", ((this.imageWidth / 2) - 21) + "px")
            .css("top", ((this.imageHeight / 2) - 5) + "px")
            .attr("src", 'data:image/gif;base64,R0lGODlhKwALAPEAAP///2dnZ7S0tGdnZyH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAKwALAAACMoSOCMuW2diD88UKG95W88uF4DaGWFmhZid93pq+pwxnLUnXh8ou+sSz+T64oCAyTBUAACH5BAkKAAAALAAAAAArAAsAAAI9xI4IyyAPYWOxmoTHrHzzmGHe94xkmJifyqFKQ0pwLLgHa82xrekkDrIBZRQab1jyfY7KTtPimixiUsevAAAh+QQJCgAAACwAAAAAKwALAAACPYSOCMswD2FjqZpqW9xv4g8KE7d54XmMpNSgqLoOpgvC60xjNonnyc7p+VKamKw1zDCMR8rp8pksYlKorgAAIfkECQoAAAAsAAAAACsACwAAAkCEjgjLltnYmJS6Bxt+sfq5ZUyoNJ9HHlEqdCfFrqn7DrE2m7Wdj/2y45FkQ13t5itKdshFExC8YCLOEBX6AhQAADsAAAAAAAAAAAA=');
        this.viewerImage
            .css("visibility", "hidden");
    } else if (this.state == 'error') {
        this.messageImage
            .css("visibility", "visible")
            .css("left", ((this.imageWidth / 2) - 16) + "px")
            .css("top", ((this.imageHeight / 2) - 16) + "px")
            .attr("src", 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAABuUlEQVRYR+2WzStEURjGjUSxQRbKmmwoaRoln2VpYams2IjN/B0WSpQi/gILZafkqzSjSFIWFspCUcjHLFD4PTVXd2bujHPOjKapOfVrzr3nfZ/z3Ldz3zuhiiKPUJH3rygbKIkKDHNOxmEAGqES7uEIViCWzznKVYE2hFeh/48NDlmfgBsXI9kM6Gk3od5Q9JG4MTgwjP8NCzLQymocGizFHogPw7VNXpCBXQQGbUR8sXfMX6AJEslzMsfvSTa9dAMjBG47bp4t7ZuFRYiC5ikj3cAaq5MWBoaI1VuxY5CzkDSR08Alq+0GYl6I9wAZTxag8cW9bjjzr/krUMXCM9T+kwHJXsE8rMOHbngGmplvQK/F5v58kwr4pU+5GIVbGaiBPeix3DwfA8q9gLAMTMOyw+b5GlB+VAbUyyOOBtSm9Raogi4jLgNPYNpyXTbJlZOQgTeoK7Syod6rDBzrMBgmpIfZNKKgLWIyMAtLjgb6kmdg3zF/xnsNVYVORxHXNHXEiNeIWrjYgi5XNcu8lEbk5VYz0YdoCjpADaqQ4x2xc9AHT634U+Il8ae0kFXI0CpXoFyBolfgB4WmQnzRD121AAAAAElFTkSuQmCC');
        this.viewerImage
            .css("visibility", "hidden");
    } else {
        this.messageImage
            .css("visibility", "hidden");
        this.viewerImage
            .css("visibility", "visible");
    }

    if (scale != 1) {
        this.viewer
            .css("transform", 'scale(' + scale + ', ' + scale + ')')
            .css("transformOrigin", '0% 0%');
    }

    this.viewerImage
        .css("margin-left", "-" + this.imageWidth + "px")
        .css("width", (this.imageWidth * 2) + "px")
        .css("height", this.imageHeight + "px");
}