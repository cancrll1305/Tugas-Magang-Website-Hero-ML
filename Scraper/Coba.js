const axios = require("axios");
const cheerio = require("cheerio");
const fs = require("fs");

async function scrapeHeroes() {
  const listUrl =
    "https://mobile-legends.fandom.com/api.php?action=query&list=categorymembers&cmtitle=Category:Heroes&cmlimit=500&format=json";

  try {
    const { data } = await axios.get(listUrl);

    const pages = data.query.categorymembers.filter(p => p.ns === 0);

    const heroes = [];

    for (const page of pages) {
      const detailUrl = `https://mobile-legends.fandom.com/api.php?action=parse&page=${encodeURIComponent(
        page.title
      )}&format=json`;

      try {
        const { data: detail } = await axios.get(detailUrl);
        const html = detail.parse.text["*"];
        const $ = cheerio.load(html);

        const infobox = $(".portable-infobox");

        const name = page.title;
        const icon = infobox.find("img").first().attr("src") || "";

        let role = "";
        let releaseDate = "";
        let specialty = "";
        let lane = "";
        let region = "";
        let price = "";

        infobox.find(".pi-data").each((i, el) => {
          const label = $(el).find(".pi-data-label").text().trim();
          const value = $(el).find(".pi-data-value").text().trim();

          if (label.includes("Role")) role = value;
          if (label.includes("Release")) releaseDate = value;
          if (label.includes("Special")) specialty = value;
          if (label.includes("Lane")) lane = value;
          if (label.includes("Region")) region = value;
          if (label.includes("Price")) price = value;
        });

        // ✅ VALIDASI WAJIB
        if (name && role && releaseDate) {
          heroes.push({
            name,
            icon,
            role,
            specialty,
            lane,
            region,
            price,
            releaseDate
          });

          console.log("✔ Added:", name);
        }
      } catch (err) {
        console.log("Skip:", page.title);
      }
    }

    fs.writeFileSync(
      "mlbb-heroes.json",
      JSON.stringify(heroes, null, 2)
    );

    console.log("✅ DONE! Total hero:", heroes.length);
  } catch (error) {
    console.log("ERROR:", error.message);
  }
}

scrapeHeroes();