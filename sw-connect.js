if ("serviceWorker" in navigator) {
    window.addEventListener("load", function () {
      // Register the service worker
      navigator.serviceWorker.register("sw.js").then(
        function (registration) {
          console.log(
            "ServiceWorker registration successful with scope: ",
            registration.scope
          );
        },
        function (error) {
          console.error("ServiceWorker registration failed: ", error);
        }
      );
  
      // Detect iOS devices for special prompt
      var isIpad = function () {
        var ua = window.navigator.userAgent.toLowerCase();
        return /ipad/.test(ua);
      };
  
      // Check if it's an iPhone or iPad and not running in standalone mode
      if (
        (function () {
          var ua = window.navigator.userAgent.toLowerCase();
          return /iphone|ipad|ipod/.test(ua);
        })() &&
        !("standalone" in window.navigator && window.navigator.standalone)
      ) {
        // Create a div to show the "Add to Home Screen" prompt for iOS users
        var addToHomeScreenDiv = document.createElement("div");
        addToHomeScreenDiv.style.cssText =
          "display: block;position: fixed;z-index:1000000;padding: 5px 7px;left: 2%;" +
          (isIpad() ? "top:15px;" : "bottom: 15px;") +
          "width: 96%;border-radius: 3px;background-color: #f1f1f1;font-size: 14px;font-family: sans-serif;text-align: center;";
        
        addToHomeScreenDiv.innerHTML =
          '<span id="triangle-down" style="' +
          (isIpad() ? "opacity:0;" : "opacity:1;") +
          'position: absolute;width: 0;height: 0;bottom: -7px;left: 50%;transform: translateX(-50%);border-left: 7px solid transparent;border-right: 7px solid transparent;border-top: 7px solid #fff;"></span><span>Install this webapp on your device: tap <span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 50 50" height="15px" id="Layer_1" version="1.1" viewBox="0 0 50 50" width="15px" xml:space="preserve"><polyline fill="none" points="17,10 25,2 33,10   " stroke="#000000" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"/><line fill="none" stroke="#000000" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2" x1="25" x2="25" y1="32" y2="2.333"/><rect fill="none" height="50" width="50"/><path d="M17,17H8v32h34V17h-9" fill="none" stroke="#000000" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"/></svg></span> and then Add to homescreen.</span>';
        
        window.document.body.appendChild(addToHomeScreenDiv);
        
        // Remove the prompt after 4 seconds
        setTimeout(function () {
          window.document.body.removeChild(addToHomeScreenDiv);
        }, 4000);
      }
    });
  }
  