const axios = require("axios");
const cheerio = require("cheerio");
const fs = require("fs");
const path = require("path");

console.log("ML Scraper Started...");

function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function fetchJson(url) {
  const { data } = await axios.get(url, {
    headers: { "User-Agent": "Mozilla/5.0" },
    responseType: "arraybuffer"
  });

  return JSON.parse(Buffer.from(data).toString("utf8"));
}

function countMojibake(value) {
  return (String(value || "").match(/Ã.|Â|â.|ðŸ|�/g) || []).length;
}

function fixEncoding(value) {
  const input = String(value || "");

  try {
    const repaired = Buffer.from(input, "latin1").toString("utf8");
    return countMojibake(repaired) < countMojibake(input) ? repaired : input;
  } catch {
    return input;
  }
}

function normalizeText(value) {
  return fixEncoding(value)
    .replace(/\u00a0/g, " ")
    .replace(/\s+/g, " ")
    .replace(/,([^\s])/g, ", $1")
    .replace(/([a-z0-9])([A-Z])/g, "$1 $2")
    .replace(/([A-Za-z])\(/g, "$1 (")
    .replace(/\)([A-Za-z])/g, ") $1")
    .replace(/(\d)or(\d)/gi, "$1 or $2")
    .trim();
}

function stripReferences(value) {
  return normalizeText(value).replace(/\[(?:citation needed|\d+)\]/gi, "").trim();
}

function cleanImage(url) {
  if (!url) return "";

  let cleaned = String(url).split(" ")[0].trim();

  if (cleaned.startsWith("//")) {
    cleaned = `https:${cleaned}`;
  }

  cleaned = cleaned.replace(/\/revision\/.*$/, "");
  cleaned = cleaned.replace(/\/scale-to-width-down\/\d+/, "");

  return cleaned;
}

function getImage(imgTag) {
  if (!imgTag || !imgTag.length) return "";

  const src =
    imgTag.attr("data-src") ||
    imgTag.attr("srcset") ||
    imgTag.attr("src");

  if (!src || src.startsWith("data:image")) {
    return "";
  }

  return cleanImage(src);
}

function getNodeText($, node) {
  if (!node) return "";

  if (!node.type && node[0]) {
    node = node[0];
  }

  if (node.type === "text") {
    return fixEncoding(node.data || "")
      .replace(/\u00a0/g, " ")
      .replace(/\[(?:citation needed|\d+)\]/gi, "");
  }

  if (node.type !== "tag") {
    return "";
  }

  const $node = $(node);
  const tag = (node.tagName || "").toLowerCase();

  if (tag === "br") {
    return "\n";
  }

  if (tag === "li") {
    return stripReferences($node.text());
  }

  if (tag === "dt") {
    return stripReferences($node.text());
  }

  if (tag === "dd") {
    return stripReferences($node.text());
  }

  if (tag === "img") {
    return "";
  }

  if (tag === "p") {
    const text = $node.contents().map((_, child) => getNodeText($, child)).get().join("");
    return `\n${text}\n`;
  }

  if (tag === "div" || tag === "section") {
    const text = $node.contents().map((_, child) => getNodeText($, child)).get().join("");
    return `${text}\n`;
  }

  if (tag === "ul" || tag === "ol") {
    return $node
      .children("li")
      .map((_, el) => stripReferences($(el).text()))
      .get()
      .filter(Boolean)
      .join("\n");
  }

  if (tag === "dl") {
    return $node
      .children()
      .map((_, child) => getNodeText($, child))
      .get()
      .filter(Boolean)
      .join("\n");
  }

  return $node.contents().map((_, child) => getNodeText($, child)).get().join("");
}

function getElementText($, el) {
  const node = el && !el.type && el[0] ? el[0] : el;
  return getNodeText($, node)
    .replace(/[ \t]+\n/g, "\n")
    .replace(/\n[ \t]+/g, "\n")
    .replace(/\n{3,}/g, "\n\n")
    .split("\n")
    .map(line => normalizeText(line))
    .join("\n")
    .trim();
}

function getParagraphTexts($, elements) {
  return elements
    .map((_, el) => getElementText($, el))
    .get()
    .map(stripReferences)
    .filter(Boolean);
}

function getListTexts($, container) {
  return container
    .find("li")
    .map((_, el) => stripReferences($(el).text()))
    .get()
    .filter(Boolean);
}

function getLinks($, container) {
  return container
    .find("a[href]")
    .map((_, el) => {
      const href = $(el).attr("href");
      const label = stripReferences($(el).text());

      if (!href || !label) return null;

      const isExternal =
        href.startsWith("http") ||
        $(el).hasClass("external") ||
        $(el).hasClass("extiw");

      if (!isExternal) return null;

      if (href.includes("static.wikia.nocookie.net")) {
        return null;
      }

      const absoluteHref = href.startsWith("http")
        ? href
        : `https://mobile-legends.fandom.com${href}`;

      return {
        label,
        url: absoluteHref
      };
    })
    .get()
    .filter(Boolean);
}

function getHeadlineText($, heading) {
  return normalizeText($(heading).text().replace(/\[.*?\]/g, ""));
}

function parseKeyValueTable($, table, fallbackTitle = "") {
  const rows = [];
  let headers = [];

  table.find("tr").each((rowIndex, row) => {
    const cells = $(row).children("th, td");

    if (!cells.length) return;

    const values = cells
      .map((_, cell) => stripReferences(getElementText($, cell)))
      .get()
      .filter(value => value !== "");

    if (!values.length) return;

    if (!headers.length && $(row).children("th").length >= 2) {
      headers = values;
      return;
    }

    rows.push(values);
  });

  const caption = stripReferences(table.find("caption").first().text());

  return {
    title: caption || fallbackTitle,
    headers,
    rows
  };
}

function parseScalingTable($, table, fallbackTitle = "") {
  const rows = [];
  let headers = [];

  table.find("tr").each((rowIndex, row) => {
    const values = $(row)
      .children("th, td")
      .map((_, cell) => stripReferences(getElementText($, cell)))
      .get()
      .filter(Boolean);

    if (!values.length) return;

    if (!headers.length) {
      headers = values;
      return;
    }

    rows.push(values);
  });

  return {
    title: fallbackTitle,
    headers,
    rows
  };
}

function parseInfoValue($, valueEl) {
  if (!valueEl || !valueEl.length) return "";

  const listItems = valueEl.children("ul, ol").children("li");

  if (listItems.length) {
    return listItems
      .map((_, el) => stripReferences($(el).text()))
      .get()
      .filter(Boolean)
      .join(" | ");
  }

  return getElementText($, valueEl)
    .split(/\n+/)
    .map(stripReferences)
    .filter(Boolean)
    .join(" | ");
}

function parsePiDataItem($, itemEl) {
  const label = stripReferences(
    itemEl.children(".pi-data-label").first().text() ||
      itemEl.find("> .pi-data-label").first().text()
  );

  const valueEl = itemEl.children(".pi-data-value").first().length
    ? itemEl.children(".pi-data-value").first()
    : itemEl.find("> .pi-data-value").first();

  const value = parseInfoValue($, valueEl);

  return {
    label,
    value,
    list: getListTexts($, valueEl),
    links: getLinks($, valueEl)
  };
}

function parseInfobox($, infobox) {
  const parsed = {
    title: "",
    image: "",
    data: {},
    sections: [],
    links: []
  };

  if (!infobox || !infobox.length) {
    return parsed;
  }

  let currentSection = {
    title: "Main",
    items: []
  };

  function pushSection() {
    if (currentSection.items.length) {
      parsed.sections.push(currentSection);
    }
  }

  infobox.children().each((_, child) => {
    const el = $(child);

    if (el.hasClass("pi-title")) {
      parsed.title = stripReferences(el.text());
      return;
    }

    if (el.hasClass("pi-image") && !parsed.image) {
      parsed.image = getImage(el.find("img").first());
      return;
    }

    if (el.hasClass("pi-header")) {
      pushSection();
      currentSection = {
        title: stripReferences(el.text()) || "Section",
        items: []
      };
      return;
    }

    if (el.hasClass("pi-data")) {
      const item = parsePiDataItem($, el);

      if (item.label) {
        parsed.data[item.label] = item.value;
      }

      if (item.links.length) {
        parsed.links.push(...item.links);
      }

      currentSection.items.push(item);
      return;
    }

    if (el.hasClass("pi-group")) {
      const sectionTitle =
        stripReferences(el.find("> .pi-header").first().text()) || "Group";

      const section = {
        title: sectionTitle,
        items: []
      };

      el.find("> .pi-data").each((__, dataItem) => {
        const item = parsePiDataItem($, $(dataItem));

        if (item.label) {
          parsed.data[item.label] = item.value;
        }

        if (item.links.length) {
          parsed.links.push(...item.links);
        }

        section.items.push(item);
      });

      if (section.items.length) {
        parsed.sections.push(section);
      }
    }
  });

  pushSection();

  return parsed;
}

function findSectionHeading($, text) {
  return $("h2").filter((_, el) => getHeadlineText($, el).toLowerCase() === text.toLowerCase()).first();
}

function getSectionNodes($, startHeading) {
  const nodes = [];
  let current = startHeading.next();

  while (current.length) {
    const tag = (current[0].tagName || "").toLowerCase();

    if (tag === "h2") break;

    nodes.push(current);
    current = current.next();
  }

  return nodes;
}

function parseLead($, body) {
  const lead = {
    quote: "",
    summary: []
  };

  let current = body.children().first();

  while (current.length) {
    if (current.hasClass("toc")) break;

    if (current.is("table.cquote2") && !lead.quote) {
      lead.quote = stripReferences(current.text());
    }

    if (current.is("p")) {
      const text = stripReferences(current.text());

      if (text) {
        lead.summary.push(text);
      }
    }

    current = current.next();
  }

  return lead;
}

function parseStats($) {
  const stats = [];

  $("table.wikitable").each((_, table) => {
    const text = normalizeText($(table).text()).toLowerCase();

    if (!text.includes("hero stats") || !text.includes("base stats")) {
      return;
    }

    $(table)
      .find("tr")
      .each((__, row) => {
        const cols = $(row).children("td");

        if (cols.length >= 4) {
          stats.push({
            attribute: stripReferences(getElementText($, cols[0])),
            level1: stripReferences(getElementText($, cols[1])),
            level15: stripReferences(getElementText($, cols[2])),
            growth: stripReferences(getElementText($, cols[3]))
          });
        }
      });
  });

  return stats;
}

function parseStory($) {
  const storyHeading = findSectionHeading($, "Story");

  const result = {
    quote: "",
    intro: [],
    sections: {},
    infobox: null
  };

  if (!storyHeading.length) {
    return result;
  }

  const nodes = getSectionNodes($, storyHeading);
  let currentSubsection = "intro";

  for (const node of nodes) {
    if (node.is("aside.portable-infobox") && !result.infobox) {
      result.infobox = parseInfobox($, node);
      continue;
    }

    if (node.is("table.cquote2") && !result.quote) {
      result.quote = stripReferences(node.text());
      continue;
    }

    if (node.is("h3")) {
      currentSubsection = getHeadlineText($, node).toLowerCase();
      result.sections[currentSubsection] = result.sections[currentSubsection] || [];
      continue;
    }

    if (node.is("p, dl, ul, ol")) {
      const text = stripReferences(getElementText($, node));

      if (!text) continue;

      if (currentSubsection === "intro") {
        result.intro.push(text);
      } else {
        result.sections[currentSubsection] = result.sections[currentSubsection] || [];
        result.sections[currentSubsection].push(text);
      }
    }
  }

  return result;
}

function parseSkillDescription($, detailCell) {
  const nodes = detailCell.contents().toArray();
  const descriptionParts = [];
  let hrCount = 0;

  for (const node of nodes) {
    const tag = (node.tagName || "").toLowerCase();

    if (tag === "div" && !hrCount) {
      continue;
    }

    if (tag === "hr") {
      hrCount += 1;
      continue;
    }

    if (hrCount !== 1) {
      continue;
    }

    const text = getNodeText($, node);

    if (text) {
      descriptionParts.push(text);
    }
  }

  return descriptionParts.join("").replace(/\n{3,}/g, "\n\n").trim();
}

function parseSkillTerms($, detailCell) {
  return detailCell
    .find("dl dt")
    .map((_, term) => {
      const label = stripReferences($(term).text());
      const description = stripReferences($(term).next("dd").text());

      if (!label || !description) return null;

      return { label, description };
    })
    .get()
    .filter(Boolean);
}

function parseSkillSupplement($, cell) {
  const tables = [];
  const notes = [];
  let pendingTitle = "";

  cell.contents().each((_, child) => {
    const node = $(child);
    const tag = (child.tagName || "").toLowerCase();
    const text = stripReferences(node.text());

    if (!tag && !text) return;

    if (tag === "table") {
      const caption = stripReferences(node.find("caption").first().text());
      const tableTitle = caption || pendingTitle;

      tables.push(parseScalingTable($, node, tableTitle));
      pendingTitle = "";
      return;
    }

    if ((tag === "div" || tag === "p") && text && !node.find("table").length) {
      if (/^notes$/i.test(text)) {
        const nextList = node.next("ul, ol");

        if (nextList.length) {
          notes.push(...getParagraphTexts($, nextList.children("li")));
        }

        return;
      }

      if (text.length <= 50) {
        pendingTitle = text;
      }
    }

    if (tag === "ul" || tag === "ol") {
      const previousText = stripReferences(node.prev().text());

      if (/^notes$/i.test(previousText)) {
        return;
      }

      notes.push(...getParagraphTexts($, node.children("li")));
    }
  });

  const iconVariantTable = tables.find(table =>
    table.title.toLowerCase().includes("icon variants")
  );

  const iconVariants = iconVariantTable
    ? cell
        .find("table")
        .first()
        .find("img")
        .map((_, img) => getImage($(img)))
        .get()
        .filter(Boolean)
    : [];

  return {
    tables,
    notes,
    iconVariants
  };
}

function parseSkillTable($, type, table, variantLabel = null) {
  if (!table || !table.length) {
    return null;
  }

  const firstRow = table.find("> tbody > tr").eq(0);
  const secondRowCell = table.find("> tbody > tr").eq(1).find("> td").first();
  const firstRowCells = firstRow.children("td");
  const iconCell = firstRowCells.eq(0);
  const detailCell = firstRowCells.eq(1);

  if (firstRowCells.length < 2 || !detailCell.length) {
    return null;
  }

  const metaLine = stripReferences(detailCell.children("div").eq(1).text());

  const tags = detailCell
    .children("div")
    .eq(1)
    .find("span")
    .map((_, span) => stripReferences($(span).text()))
    .get()
    .filter(Boolean);

  const metadata = metaLine
    .split("|")
    .map(part => {
      let cleaned = normalizeText(part);

      for (const tag of tags) {
        const safeTag = tag.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
        cleaned = cleaned.replace(new RegExp(`\\b${safeTag}\\b`, "g"), "");
      }

      return normalizeText(cleaned);
    })
    .filter(Boolean)
    .filter(part => !tags.includes(part));

  const supplement = secondRowCell.length
    ? parseSkillSupplement($, secondRowCell)
    : { tables: [], notes: [], iconVariants: [] };

  return {
    type,
    name:
      stripReferences(detailCell.children("div").first().text()) || type,
    icon: getImage(iconCell.find("img").first()),
    tags,
    metadata,
    description: parseSkillDescription($, detailCell),
    terms: parseSkillTerms($, detailCell),
    tables: supplement.tables,
    notes: supplement.notes,
    iconVariants: supplement.iconVariants,
    variantLabel: variantLabel ? stripReferences(variantLabel) : null
  };
}

function parseTabberSkill($, type, tabber) {
  const labels = tabber
    .find("> .wds-tabs__wrapper .wds-tabs__tab .wds-tabs__tab-label")
    .map((_, el) => stripReferences($(el).text()))
    .get()
    .filter(Boolean);

  const contents = tabber.find("> .wds-tab__content");
  const parsedVariants = [];

  contents.each((index, content) => {
    const $content = $(content);
    let table = $content.children("table.wikitable").first();

    if (!table.length) {
      table = $content.find("table.wikitable").first();
    }

    const parsed = parseSkillTable($, type, table, labels[index] || `Variant ${index + 1}`);

    if (parsed) {
      parsedVariants.push(parsed);
    }
  });

  if (!parsedVariants.length) {
    return null;
  }

  const preferredBase =
    parsedVariants.find(variant => (variant.variantLabel || "").toLowerCase() === "default") ||
    parsedVariants[0];

  return {
    ...preferredBase,
    variants: parsedVariants.map(variant => ({
      ...variant
    }))
  };
}

function parseSkills($) {
  const abilitiesHeading = findSectionHeading($, "Abilities");

  if (!abilitiesHeading.length) {
    return [];
  }

  const skills = [];
  const nodes = getSectionNodes($, abilitiesHeading);

  for (let index = 0; index < nodes.length; index += 1) {
    const node = nodes[index];

    if (!node.is("h3")) continue;

    const type = getHeadlineText($, node);

    if (!/^(Passive|Skill 1|Skill 2|Skill 3|Ultimate)$/i.test(type)) {
      continue;
    }

    const nextNode = node.next();

    if (nextNode.is("div.tabber")) {
      const skill = parseTabberSkill($, type, nextNode);

      if (skill) {
        skills.push(skill);
      }

      continue;
    }

    const table = nextNode.is("table.wikitable")
      ? nextNode
      : node.nextAll("table.wikitable").first();

    const skill = parseSkillTable($, type, table);

    if (skill) {
      skills.push(skill);
    }
  }

  return skills;
}

function parseGallery($) {
  const galleryHeading = findSectionHeading($, "Gallery");
  const categories = {};
  const flattened = [];

  if (!galleryHeading.length) {
    return { categories, flattened };
  }

  const nodes = getSectionNodes($, galleryHeading);

  for (const node of nodes) {
    if (!node.is("h3")) continue;

    const category = getHeadlineText($, node);
    const gallery = node.next("div.wikia-gallery");

    if (!gallery.length) continue;

    const items = gallery
      .find("> .wikia-gallery-item")
      .map((_, item) => {
        const $item = $(item);
        const name =
          stripReferences($item.find(".lightbox-caption").first().text()) ||
          stripReferences($item.find("img").attr("alt"));
        const image = getImage($item.find("img").first());

        if (!name || !image) return null;

        return {
          name,
          image,
          category
        };
      })
      .get()
      .filter(Boolean);

    categories[category] = items;
    flattened.push(...items);
  }

  return { categories, flattened };
}

function dedupeLinks(links) {
  const seen = new Set();

  return links.filter(link => {
    const key = `${link.label}|${link.url}`;

    if (seen.has(key)) {
      return false;
    }

    seen.add(key);
    return true;
  });
}

function mergeInfoObjects(...objects) {
  const result = {};

  for (const object of objects) {
    for (const [key, value] of Object.entries(object || {})) {
      if (!key || !value) continue;
      result[key] = value;
    }
  }

  return result;
}

async function scrapeHeroDetail(heroName) {
  const detailUrl =
    `https://mobile-legends.fandom.com/api.php?action=parse&page=${encodeURIComponent(heroName)}&format=json`;

  try {
    const data = await fetchJson(detailUrl);

    if (!data.parse) return null;

    const html = data.parse.text["*"];
    const $ = cheerio.load(html);
    const body = $(".mw-parser-output").first();
    const mainInfobox = $("aside.portable-infobox").first();

    if (!mainInfobox.length) {
      console.log("No infobox:", heroName);
      return null;
    }

    const mainParsed = parseInfobox($, mainInfobox);
    const story = parseStory($);
    const storyParsed = story.infobox || { data: {}, sections: [], links: [] };
    const info = mergeInfoObjects(mainParsed.data, storyParsed.data);
    const stats = parseStats($);

    const isHero =
      Boolean(info.Role || info.Specialty || info["Lane Recc."]) &&
      stats.length > 0;

    if (!isHero) {
      console.log("Skipped (not hero):", heroName);
      return null;
    }

    const lead = parseLead($, body);
    const skills = parseSkills($);
    const gallery = parseGallery($);

    const relationshipsSection = storyParsed.sections.find(
      section => section.title.toLowerCase() === "relationships"
    );
    const voicedBySection = storyParsed.sections.find(
      section => section.title.toLowerCase() === "voiced by"
    );

    const hero = {
      name: heroName,
      slug: heroName.toLowerCase().replace(/\s+/g, "-"),
      url: `https://mobile-legends.fandom.com/wiki/${encodeURIComponent(heroName).replace(/%20/g, "_")}`,
      title: (mainParsed.title || heroName).replace(/^([^"]+)"([^"]+)"$/, '$1 "$2"'),
      icon: mainParsed.image || getImage(mainInfobox.find("img").first()),
      lead,
      info,
      stats,
      skills,
      skins: gallery.flattened.filter(item =>
        ["Splash art", "Painted skins", "Artwork", "Splash arts"].includes(item.category)
      ),
      gallery: gallery.categories,
      story: {
        quote: story.quote,
        intro: story.intro,
        bio: story.sections.bio || [],
        sideStory: story.sections["side story"] || [],
        sections: story.sections
      },
      infobox: {
        main: mainParsed,
        story: storyParsed
      },
      relationships: relationshipsSection
        ? relationshipsSection.items.flatMap(item => item.list.length ? item.list : item.value ? [item.value] : [])
        : [],
      voicedBy: voicedBySection
        ? voicedBySection.items.reduce((acc, item) => {
            if (item.label && item.value) {
              acc[item.label] = item.value;
            }
            return acc;
          }, {})
        : {},
      links: dedupeLinks([
        ...mainParsed.links,
        ...storyParsed.links
      ])
    };

    console.log("Hero scraped:", heroName);
    return hero;
  } catch (error) {
    console.log("Error detail:", heroName, error.message);
    return null;
  }
}

async function scrapeAllHeroes() {
  console.log("Getting hero list...");

  const listUrl =
    "https://mobile-legends.fandom.com/api.php?action=query&list=categorymembers&cmtitle=Category:Heroes&cmlimit=500&format=json";

  try {
    const data = await fetchJson(listUrl);

    let pages = data.query.categorymembers.filter(page => {
      if (page.ns !== 0) return false;

      const title = page.title.toLowerCase();

      if (title.includes("list")) return false;
      if (title.includes("guide")) return false;
      if (title.includes("role")) return false;
      if (title.includes("category")) return false;

      return true;
    });

    const filters = String(process.env.HERO_FILTER || "")
      .split(",")
      .map(value => value.trim().toLowerCase())
      .filter(Boolean);

    if (filters.length) {
      pages = pages.filter(page =>
        filters.some(filter => page.title.toLowerCase().includes(filter))
      );
    }

    const heroLimit = Number(process.env.HERO_LIMIT || 0);

    if (heroLimit > 0) {
      pages = pages.slice(0, heroLimit);
    }

    const isPartialRun = filters.length > 0 || heroLimit > 0;
    const allHeroes = [];

    for (const page of pages) {
      const heroName = page.title.trim();

      console.log("Scraping:", heroName);

      const detail = await scrapeHeroDetail(heroName);

      if (detail) {
        allHeroes.push(detail);
      }

      await delay(600);
    }

    const payload = JSON.stringify(allHeroes, null, 2);
    const scraperOutputPath = path.join(
      __dirname,
      isPartialRun ? "mlbb-heroes-detail.filtered.json" : "mlbb-heroes-detail.json"
    );
    const frontendOutputPath = path.join(
      __dirname,
      "..",
      "ml-frontend",
      "storage",
      "app",
      "private",
      "mlbb-heroes-detail.json"
    );

    fs.writeFileSync(scraperOutputPath, payload);

    if (!isPartialRun && fs.existsSync(path.dirname(frontendOutputPath))) {
      fs.writeFileSync(frontendOutputPath, payload);
    }

    console.log("DONE ALL HEROES!");
    console.log("Total Hero:", allHeroes.length);
  } catch (error) {
    console.log("ERROR:", error.message);
  }
}

scrapeAllHeroes();
