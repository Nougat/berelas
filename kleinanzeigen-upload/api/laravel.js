export async function getNextAd() {
    const response = await fetch(
            "http://localhost:8000/api/kleinanzeigen/next"
        );
    return await response.json();
}