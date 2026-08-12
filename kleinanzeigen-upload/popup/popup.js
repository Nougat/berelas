import { getNextAd } from "../api/laravel.js";

document
    .getElementById("load")
    .addEventListener("click", async () => {

        const data = await getNextAd();

        document.getElementById("output").textContent =
            JSON.stringify(data, null, 2);
    });