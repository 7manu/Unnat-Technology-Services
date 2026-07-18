document.addEventListener("DOMContentLoaded", () => {
  const staticTextForPromo = "SS METAL MORADABAD";
  const uploadImage = document.getElementById("uploadImage");
  const customText = document.getElementById("customText");
  const generateBtn = document.getElementById("generateBtn");
  const canvas = document.getElementById("posterCanvas");
  const ctx = canvas.getContext("2d");
  const gallery = document.getElementById("gallery");
//   const loadMoreBtn = document.getElementById("loadMoreBtn");
  const paginationContainer = document.createElement("div");

  paginationContainer.className = "pagination-dots text-center mt-3";
  document.querySelector(".container.mt-5").appendChild(paginationContainer);

  let uploadedImage = null;
  let brandLogo = new Image();
  brandLogo.src = "icon.png";
  let db;

  const DB_NAME = "PosterDB";
  const STORE_NAME = "posters";
  let currentPage = 1;
  const itemsPerPage = 6;
  let totalImages = 0;

  function initDB() {
    return new Promise((resolve, reject) => {
      const openRequest = indexedDB.open(DB_NAME, 1);
      openRequest.onupgradeneeded = function (event) {
        let db = event.target.result;
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          db.createObjectStore(STORE_NAME, {
            keyPath: "id",
            autoIncrement: true,
          });
        }
      };
      openRequest.onsuccess = function (event) {
        db = event.target.result;
        resolve();
      };
      openRequest.onerror = function () {
        console.error("Error opening IndexedDB");
        reject();
      };
    });
  }

  async function initializeApp() {
    await initDB();
    loadImages();
  }

  uploadImage.addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (event) {
        uploadedImage = new Image();
        uploadedImage.onload = function () {
          canvas.width = uploadedImage.width;
          canvas.height = uploadedImage.height;
          drawPoster();
        };
        uploadedImage.src = event.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

  function drawPoster() {
    if (!uploadedImage) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(uploadedImage, 0, 0, canvas.width, canvas.height);

    let logoWidth = 300;
    let logoHeight = 300;
    ctx.drawImage(brandLogo, 3, 3, logoWidth, logoHeight);

    ctx.fillStyle = "rgba(0, 0, 0, 0.7)";
    ctx.fillRect(30, canvas.height - 100, canvas.width - 60, 60);

    ctx.fillStyle = "white";
    ctx.font = "bold 36px Poppins";
    ctx.textAlign = "center";
    ctx.fillText(customText.value, canvas.width / 2, canvas.height - 60);

    ctx.fillStyle = "rgba(0, 0, 0, 0.7)";
    ctx.fillRect(0, 0, canvas.width, 60);

    ctx.fillStyle = "white";
    ctx.font = "bold 36px Poppins";
    ctx.textAlign = "center";
    ctx.fillText(staticTextForPromo, canvas.width / 2, 40);
  }

  generateBtn.addEventListener("click", function () {
    drawPoster();
    setTimeout(saveImage, 500);
  });

  function saveImage() {
    const imgData = canvas.toDataURL("image/png");

    let transaction = db.transaction(STORE_NAME, "readwrite");
    let store = transaction.objectStore(STORE_NAME);

    let newImage = { image: imgData };

    let request = store.add(newImage);

    request.onsuccess = function (event) {
      newImage.id = event.target.result;
      appendToGallery(newImage);
      showNotification("New poster added!");
      loadImages();
    };

    request.onerror = function () {
      console.error("Error saving image:", request.error);
    };
  }

  function appendToGallery(data) {
    if (!data || !data.id) {
      return;
    }

    let div = document.createElement("div");
    div.classList.add("gallery-item", "col-md-4");
    div.setAttribute("data-id", data.id);

    div.innerHTML = `
            <img src="${data.image}" class="img-fluid rounded shadow">
            <div class="d-flex justify-content-between mt-2">
                <button class="btn btn-success btn-sm" onclick="downloadImage('${data.image}')">Download</button>
                <button class="btn btn-warning btn-sm" onclick="shareImage('${data.image}')">Share</button>
                <button class="btn btn-danger btn-sm delete-btn">Delete</button>
            </div>
        `;

    div.querySelector(".delete-btn").addEventListener("click", function () {
      deleteImage(data.id, div);
    });

    gallery.appendChild(div);
  }
  function updatePaginationDots() {
    paginationContainer.innerHTML = "";

    let totalPages = Math.ceil(totalImages / itemsPerPage);

    // Create Previous Button
    let prevButton = document.createElement("button");
    prevButton.innerText = "◄ Prev";
    prevButton.className = "btn btn-light btn-sm mx-2";
    prevButton.disabled = currentPage === 1;
    prevButton.addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage--;
        loadImages();
      }
    });
    paginationContainer.appendChild(prevButton);

    // Create Pagination Dots
    for (let i = 1; i <= totalPages; i++) {
      let dot = document.createElement("span");
      dot.className = "pagination-dot";
      dot.style.cursor = "pointer";
      dot.style.margin = "0 5px";
      dot.style.width = "12px";
      dot.style.height = "12px";
      dot.style.borderRadius = "50%";
      dot.style.display = "inline-block";
      dot.style.backgroundColor = i === currentPage ? "white" : "gray";

      dot.addEventListener("click", () => {
        currentPage = i;
        loadImages();
      });

      paginationContainer.appendChild(dot);
    }

    // Create Next Button
    let nextButton = document.createElement("button");
    nextButton.innerText = "Next ►";
    nextButton.className = "btn btn-light btn-sm mx-2";
    nextButton.disabled = currentPage === totalPages;
    nextButton.addEventListener("click", () => {
      if (currentPage < totalPages) {
        currentPage++;
        loadImages();
      }
    });
    paginationContainer.appendChild(nextButton);
  }

  function showNotification(message) {
    if (Notification.permission === "granted") {
      new Notification("Promo Poster", { body: message });
    } else if (Notification.permission !== "denied") {
      Notification.requestPermission().then((permission) => {
        if (permission === "granted") {
          new Notification("Promo Poster", { body: message });
        }
      });
    }
  }

  function loadImages() {
    if (!db) {
      return;
    }

    let transaction = db.transaction(STORE_NAME, "readonly");
    let store = transaction.objectStore(STORE_NAME);
    let request = store.getAll();

    request.onsuccess = function () {
      let images = request.result;

      totalImages = images.length;

      if (Array.isArray(images)) {
        images = images.filter((data) => data.id).sort((a, b) => b.id - a.id);

        let startIndex = (currentPage - 1) * itemsPerPage;
        let paginatedImages = images.slice(
          startIndex,
          startIndex + itemsPerPage
        );

        gallery.innerHTML = "";
        paginatedImages.forEach(appendToGallery);

        updatePaginationDots();
        // loadMoreBtn.style.display =
        //   startIndex + itemsPerPage < totalImages ? "block" : "none";
      } else {
        console.error("Unexpected data format:", request.result);
      }
    };

    request.onerror = function () {
      console.error("Error loading images:", request.error);
    };
  }

//   loadMoreBtn.addEventListener("click", function () {
//     currentPage++;
//     loadImages();
//   });

  window.deleteImage = function (id, element) {
    if (!id) {
      return;
    }

    let transaction = db.transaction(STORE_NAME, "readwrite");
    let store = transaction.objectStore(STORE_NAME);
    let request = store.delete(id);

    request.onsuccess = function () {
      element.remove();
      showNotification("Poster deleted!");
      loadImages();
    };

    request.onerror = function () {
      console.error("Error deleting image:", request.error);
    };
  };

  window.downloadImage = function (imageSrc) {
    const link = document.createElement("a");
    link.href = imageSrc;
    link.download = "poster.png"; // Sets the download filename
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  window.shareImage = function (imageSrc) {
    if (navigator.share) {
      fetch(imageSrc)
        .then((response) => response.blob())
        .then((blob) => {
          const file = new File([blob], "poster.png", { type: "image/png" });
          navigator
            .share({
              title: "Check out this poster!",
              text: "I created this poster using the app!",
              files: [file],
            })
            .then(() => console.log("Shared successfully"))
            .catch((err) => console.error("Error sharing:", err));
        })
        .catch((err) => console.error("Error fetching image:", err));
    } else {
      alert("Sharing is not supported on this device.");
    }
  };

  initializeApp();
});
