$(document).ready(function () {
    
    $(window).on("scroll", function () {
        let scrollTop = $(window).scrollTop();
        let maxScroll = 240; // Adjust as needed
        let lastScrollTop = 0; // To track scroll direction
        // Normalize scroll value between 0 and 1
        let scrollProgress = Math.min(scrollTop / maxScroll, 1);

        // Calculate clip-path transition based on scroll
        let clipTop = 30.46 - (30.46 * (scrollTop / maxScroll));
        let opacity = 1 - (1 * (1 - scrollProgress)); // Fades out when scrolling up

        // Apply styles
        $("[bg-layer]").css({
            "clip-path": `inset(${clipTop}% round 40px)`, 
            "opacity": opacity
        });

    });
        
    let isPoppedUp = false;
    let fusionTrigger = 120;
    $(window).on("scroll", function () {
        let scrollTop = $(window).scrollTop();
        let maxScroll = 100; // Adjust as needed

        // Ensure progress is between 0 and 1
        let progress = Math.min(scrollTop / maxScroll, 1);

        // Get viewport dimensions
        let vw = $(window).width() / 100;
        let vh = $(window).height() / 100;

        // Define initial and target values
        let clipTopStart = 29.85 * vh, clipTopEnd = 451;
        let clipRightStart = 39.85 * vw, clipRightEnd = 29.6516 * vw;
        let borderRadiusStart = 31.3, borderRadiusEnd = 5.5088;
        let scaleStart = 1, scaleEnd = 0.3;
        let opacityStart = 0, opacityEnd = 1;
        let laptopScaleStart = 1, laptopScaleEnd = 0.4;

        // Compute interpolated values
        let clipTop = clipTopStart + (clipTopEnd - clipTopStart) * progress;
        let clipRight = clipRightStart + (clipRightEnd - clipRightStart) * progress;
        let borderRadius = borderRadiusStart + (borderRadiusEnd - borderRadiusStart) * progress;
        let scale = scaleStart + (scaleEnd - scaleStart) * progress;
        let opacity = opacityStart + (opacityEnd - opacityStart) * progress;
        let laptopScale = laptopScaleStart + (laptopScaleEnd - laptopScaleStart) * progress;

        // Fade out .bg-img.v9 img after 80px scroll
        let imgOpacity = scrollTop <= 100 ? 1 : 1 - ((scrollTop - 100) / 20);
        imgOpacity = Math.max(imgOpacity, 0); // Ensure opacity doesn't go below 0
        
        // Apply transformations dynamically
        $("img.logo-v9").css({
            // "transform": `scale(${scale})`,
            "opacity": imgOpacity,
            "transition": "opacity 0.6s ease-out"
        });

        $(".bg-img[laptop-opacity]").css("opacity", opacity);
      

        $("[laptop-bg]").css({
            "transform": `scale(${laptopScale})`,
            "transition": "transform 0.5s ease-out"
        });

        // Adjust height with smooth animation
        let maxHeight = 1862;        
        let newHeight = scrollTop === 0 ? 0 : `${maxHeight}px`;

        $("[laptop-bg-position]").css({
            "height": newHeight,
        });

        let scrollHit = 200;
        let lteOpacity = scrollTop >= scrollHit ? 1 : 0;
        lteOpacity = Math.max(lteOpacity, 0); // Ensure opacity doesn't go below 0
        
        let LteMaxY = scrollTop >= scrollHit ? -440 : 0;
        let LteMaxScale = scrollTop >= scrollHit ? .81 : 0;


        $("[lte-bg]").css({
            "opacity": lteOpacity,
            "transform": `scale(${LteMaxScale}) translateY(${LteMaxY}px)`,
            "transition": "opacity .6s ease-out, scale .3s ease-out"
        });

        var deviceStart = 200, deviceEnd = 250;
        var deviceProgress = Math.min(Math.max((scrollTop - deviceStart) / (deviceEnd - deviceStart), 0), 1); // Clamped between 0 and 1


        $(".bg-img[pad-opacity]").css({
            "opacity": progress * 1, // Direct calculation
            "transform": "translateX(" + (-150 + progress * 100) + "px)", // Direct calculation
                        "transition": "transform .4s ease-out"
        });

        $(".bg-img[cms-text]").css({
            "opacity": progress * 1, // Direct calculation
            "transform": "translateY(" + (-100 + progress * 100) + "px)", // Direct calculation
             "transition": "transform .3s ease-out"
        });

    });





});
