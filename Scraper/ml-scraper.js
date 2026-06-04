const fs = require("fs");
const path = require("path");
const axios = require("axios");

const API_BASE =
  "https://mobile-legends.fandom.com/api.php?format=json";

async function fetchJSON(url) {
  const res = await axios.get(url, {
    headers: { "User-Agent": "Mozilla/5.0" }
  });
  return res.data;
}

function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}

function cleanText(str) {
  if (!str) return "";
  return str
    .replace(/\s+/g, " ")
    .replace(/\u00a0/g, " ")
    .trim();
}

function splitMulti(str) {
  if (!str) return [];
  return str
    .split(/\/|,|\|/g)
    .map((x) => cleanText(x))
    .filter(Boolean);
}

async function getAllHeroes() {
  let cmcontinue = null;
  const heroes = [];

  while (true) {
    let url =
      `${API_BASE}` +
      `&action=query&list=categorymembers` +
      `&cmtitle=Category:Heroes` +
      `&cmlimit=500&cmnamespace=0`;

    if (cmcontinue)
      url += `&cmcontinue=${encodeURIComponent(cmcontinue)}`;

    const data = await fetchJSON(url);

    const members = data.query?.categorymembers || [];

    for (const m of members) {
      if (!m.title) continue;

      if (
        m.title.includes("Category:") ||
        m.title.toLowerCase().includes("list of")
      ) {
        continue;
      }

      heroes.push({
        name: m.title,
        pageid: m.pageid,
        url: `https://mobile-legends.fandom.com/wiki/${encodeURIComponent(
          m.title.replace(/ /g, "_")
        )}`,
      });
    }

    if (!data.continue?.cmcontinue) break;
    cmcontinue = data.continue.cmcontinue;
  }

  return heroes;
}

async function getHeroDetail(hero) {
  try {
    const url =
      `${API_BASE}` +
      `&action=parse&pageid=${hero.pageid}` +
      `&prop=text`;

    const data = await fetchJSON(url);

    const html = data.parse?.text?.["*"];
    if (!html) {
      return {
        ...hero,
        roles: ["Unknown"],
        specialties: [],
        lane: [],
        image: "",
      };
    }

    const getField = (label) => {
      const regex = new RegExp(
        `<div[^>]*class="pi-data-label[^"]*"[^>]*>${label}<\\/div>[\\s\\S]*?<div[^>]*class="pi-data-value[^"]*"[^>]*>([\\s\\S]*?)<\\/div>`,
        "i"
      );

      const match = html.match(regex);
      if (!match) return "";

      let val = match[1];
      val = val.replace(/<br\s*\/?>/gi, "/");
      val = val.replace(/<[^>]+>/g, "");
      return cleanText(val);
    };

    const roles = splitMulti(
      getField("Role") ||
      getField("Roles")
    );

    const specialties = splitMulti(
      getField("Specialty") ||
      getField("Specialties")
    );

    const lane = splitMulti(
      getField("Lane") ||
      getField("Lane Recommendation")
    );

    let image = "";
    const ogMatch = html.match(
      /property="og:image"\s*content="([^"]+)"/i
    );
    if (ogMatch) image = ogMatch[1];

    if (image.includes("/revision"))
      image = image.split("/revision")[0];

    return {
      ...hero,
      roles: roles.length ? roles : ["Unknown"],
      specialties,
      lane,
      image,
    };
  } catch (err) {
    console.log("❌ Error:", hero.name);
    return {
      ...hero,
      roles: ["Unknown"],
      specialties: [],
      lane: [],
      image: "",
    };
  }
}

(async () => {
  try {
    console.log("🌐 Getting hero list...");

    const heroList = await getAllHeroes();
    console.log(`📌 Found ${heroList.length} hero pages`);

    const finalData = [];
    let id = 1;

    for (const hero of heroList) {
      console.log("🔍 Scraping:", hero.name);

      const detail = await getHeroDetail(hero);

      finalData.push({
        id: id++,
        name: detail.name,
        roles: detail.roles,
        specialties: detail.specialties,
        lane: detail.lane,
        image: detail.image,
        url: detail.url,
      });

      await sleep(300);
    }

    const dataDir = path.join(__dirname, "data");
    if (!fs.existsSync(dataDir))
      fs.mkdirSync(dataDir);

    const outputPath = path.join(dataDir, "ml-heroes.json");
    fs.writeFileSync(
      outputPath,
      JSON.stringify(finalData, null, 2)
    );

    console.log("\n🎉 DONE!");
    console.log("Saved to:", outputPath);
  } catch (err) {
    console.log("FATAL ERROR:", err.message);
  }
})();