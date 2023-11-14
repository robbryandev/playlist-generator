const selectedArtists = [];

export function handleArtistSelection() {
  const searchResults = document.getElementById("artist-results");
  if (searchResults) {
    const observer = new MutationObserver(() => {
      const artistElements = document.querySelectorAll(".search-result");
      artistElements.forEach((elm) => {
        elm.addEventListener("click", () => {
          const artistName = elm.querySelector("h1").innerText;
          const artistId = elm.id;
          console.log(`${artistName}: ${artistId}`);
          if (!selectedArtists.includes(artistId)) {
            selectedArtists.push(artistId);
          }
          console.log(selectedArtists);
        });
      });
    });

    const observerConfig = {
      childList: true,
      attributes: false,
      subtree: false
    };

    observer.observe(searchResults, observerConfig);
  }
}