from __future__ import annotations

import io
import math
import re
import textwrap
from datetime import datetime
from html import unescape
from pathlib import Path
from typing import Iterable
from urllib.parse import urlparse
from urllib.request import urlopen, Request

from PIL import Image, ImageColor, ImageDraw, ImageFont
import json

ROOT = Path(__file__).resolve().parents[1]
SCREENSHOT_DIR = ROOT / "docs" / "screenshots"
GRAPHQL_URL = "http://127.0.0.1:8090/graphql"

COLORS = {
    "bg": "#ffffff",
    "surface": "#ffffff",
    "surface_alt": "#f6f8fb",
    "text": "#263247",
    "muted": "#7a7f8d",
    "nav": "#454f61",
    "primary": "#4a82ed",
    "primary_hover": "#3972e0",
    "border": (38, 50, 71, 31),
    "shadow": (21, 29, 45, 18),
}


def post_json(query: str, variables: dict | None = None) -> dict:
    body = json.dumps({"query": query, "variables": variables or {}}).encode("utf-8")
    request = Request(
        GRAPHQL_URL,
        data=body,
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    with urlopen(request, timeout=30) as response:
        return __import__("json").loads(response.read().decode("utf-8"))


LATEST_POSTS_QUERY = """
query LatestPosts {
  posts(first: 6, where: { orderby: { field: DATE, order: DESC } }) {
    nodes {
      title
      slug
      excerpt
      date
      content
      featuredImage {
        node {
          sourceUrl
          altText
        }
      }
    }
  }
}
"""

POST_BY_SLUG_QUERY = """
query PostBySlug($slug: ID!) {
  post(id: $slug, idType: SLUG) {
    title
    slug
    excerpt
    date
    content
    featuredImage {
      node {
        sourceUrl
        altText
      }
    }
  }
}
"""


def load_font(name: str, size: int) -> ImageFont.FreeTypeFont:
    candidates = {
        "regular": [
            Path("C:/Windows/Fonts/segoeui.ttf"),
            Path("C:/Windows/Fonts/arial.ttf"),
        ],
        "bold": [
            Path("C:/Windows/Fonts/segoeuib.ttf"),
            Path("C:/Windows/Fonts/arialbd.ttf"),
        ],
    }
    for path in candidates[name]:
        if path.exists():
            return ImageFont.truetype(str(path), size=size)
    raise FileNotFoundError(f"Could not find a font for {name}.")


FONT_11 = load_font("regular", 11)
FONT_12 = load_font("regular", 12)
FONT_13 = load_font("regular", 13)
FONT_14 = load_font("regular", 14)
FONT_15B = load_font("bold", 15)
FONT_28B = load_font("bold", 28)
FONT_32B = load_font("bold", 32)
FONT_42B = load_font("bold", 42)
FONT_48B = load_font("bold", 48)


def clean_text(html_text: str) -> str:
    text = re.sub(r"<[^>]+>", " ", html_text)
    text = unescape(text)
    text = re.sub(r"\s+", " ", text)
    return text.strip()


def format_date(value: str) -> str:
    dt = datetime.fromisoformat(value.replace("Z", "+00:00"))
    return dt.strftime("%B %d, %Y").replace(" 0", " ")


def fetch_image(url: str | None, size: tuple[int, int]) -> Image.Image:
    return make_placeholder_art(size, url or "")


def crop_cover(image: Image.Image, size: tuple[int, int]) -> Image.Image:
    src_ratio = image.width / image.height
    dst_ratio = size[0] / size[1]
    if src_ratio > dst_ratio:
        new_height = size[1]
        new_width = int(new_height * src_ratio)
    else:
        new_width = size[0]
        new_height = int(new_width / src_ratio)
    resized = image.resize((new_width, new_height), Image.Resampling.LANCZOS)
    left = (new_width - size[0]) // 2
    top = (new_height - size[1]) // 2
    return resized.crop((left, top, left + size[0], top + size[1]))


def draw_text_block(draw: ImageDraw.ImageDraw, x: int, y: int, text: str, font, fill, max_width: int, line_spacing: int = 4) -> int:
    lines = wrap_text(draw, text, font, max_width)
    cursor = y
    for line in lines:
        draw.text((x, cursor), line, font=font, fill=fill)
        cursor += font.size + line_spacing
    return cursor


def wrap_text(draw: ImageDraw.ImageDraw, text: str, font, max_width: int) -> list[str]:
    words = text.split()
    lines: list[str] = []
    current: list[str] = []
    for word in words:
        probe = " ".join(current + [word])
        width = draw.textbbox((0, 0), probe, font=font)[2]
        if width <= max_width or not current:
            current.append(word)
        else:
            lines.append(" ".join(current))
            current = [word]
    if current:
        lines.append(" ".join(current))
    return lines


def rounded_rectangle(draw: ImageDraw.ImageDraw, box: tuple[int, int, int, int], radius: int, fill, outline=None):
    draw.rounded_rectangle(box, radius=radius, fill=fill, outline=outline)


def draw_button(draw: ImageDraw.ImageDraw, xy: tuple[int, int], label: str, font, fill: str):
    x, y = xy
    w, h = 126, 44
    rounded_rectangle(draw, (x, y, x + w, y + h), 999, fill=fill)
    bbox = draw.textbbox((0, 0), label, font=font)
    tx = x + (w - (bbox[2] - bbox[0])) // 2
    ty = y + (h - (bbox[3] - bbox[1])) // 2 - 1
    draw.text((tx, ty), label, font=font, fill="white")


def draw_card(draw: ImageDraw.ImageDraw, canvas: Image.Image, post: dict, x: int, y: int, width: int, mobile: bool = False):
    card_height = 242 if not mobile else 312
    image_height = 128 if not mobile else 184
    rounded_rectangle(draw, (x, y, x + width, y + card_height), 6, fill="white", outline=ImageColor.getrgb("#dfe4ed"))
    thumb = fetch_image(post["featuredImage"]["node"]["sourceUrl"], (width, image_height))
    canvas.alpha_composite(thumb, (x, y))
    body_x = x + 12
    title_max_width = width - 24
    title_y = y + image_height + 10
    title_lines = wrap_text(draw, post["title"], FONT_15B, title_max_width)
    cursor = title_y
    for line in title_lines[:2]:
        draw.text((body_x, cursor), line, font=FONT_15B, fill=COLORS["text"])
        cursor += 18
    excerpt = clean_text(post["excerpt"])
    excerpt_lines = wrap_text(draw, excerpt, FONT_12, title_max_width)
    for line in excerpt_lines[:2]:
        draw.text((body_x, cursor), line, font=FONT_12, fill=COLORS["muted"])
        cursor += 16

    footer_y = y + card_height - 28
    avatar_box = (body_x, footer_y - 2, body_x + 20, footer_y + 18)
    rounded_rectangle(draw, avatar_box, 999, fill="#eef2f8", outline="#d6dde8")
    draw.text((body_x + 5, footer_y + 2), "JD", font=FONT_11, fill="#445063")
    draw.text((body_x + 26, footer_y + 2), "John Doe", font=FONT_11, fill="#445063")
    draw.text((x + width - 108, footer_y + 2), format_date(post["date"]), font=FONT_11, fill=COLORS["muted"])
    draw.text((x + width - 54, footer_y + 2), "Read More", font=FONT_11, fill=COLORS["primary"])


def hero_background(size: tuple[int, int]) -> Image.Image:
    image = Image.new("RGBA", size, "#2e3444")
    draw = ImageDraw.Draw(image)
    for y in range(size[1]):
        blend = y / max(size[1] - 1, 1)
        color = (
            int(46 + blend * 10),
            int(52 + blend * 8),
            int(68 + blend * 16),
            255,
        )
        draw.line([(0, y), (size[0], y)], fill=color)
    if size[0] <= 480:
        draw.ellipse((size[0] - 150, -20, size[0] + 90, 140), fill=(255, 255, 255, 18))
        draw.ellipse((size[0] - 80, 140, size[0] + 80, 260), fill=(255, 255, 255, 10))
    else:
        draw.ellipse((size[0] - 320, -40, size[0] + 80, 220), fill=(255, 255, 255, 18))
        draw.ellipse((size[0] - 180, 60, size[0] + 140, 300), fill=(255, 255, 255, 10))
    return image


def make_placeholder_art(size: tuple[int, int], seed_text: str) -> Image.Image:
    palette = [
        ("#f6e6a8", "#2d3a4e"),
        ("#d9e8fb", "#2d4a73"),
        ("#e7e0ff", "#443569"),
        ("#d9f2ec", "#25534d"),
        ("#f5dada", "#6d3636"),
        ("#e7ecf8", "#2b3952"),
    ]
    index = sum(ord(ch) for ch in seed_text) % len(palette)
    bg, fg = palette[index]
    image = Image.new("RGBA", size, bg)
    draw = ImageDraw.Draw(image)
    draw.rounded_rectangle((18, 18, size[0] - 18, size[1] - 18), radius=16, fill=bg, outline=(255, 255, 255, 55))
    if size[0] > 300 and size[1] > 180:
        draw.rounded_rectangle((48, 46, size[0] - 48, size[1] - 46), radius=18, fill="white")
        draw.rectangle((72, 74, size[0] - 72, size[1] - 74), outline=fg, width=8)
        draw.ellipse((size[0] // 2 - 28, size[1] // 2 - 28, size[0] // 2 + 28, size[1] // 2 + 28), fill=fg)
    else:
        line_gap = max(16, size[1] // 5)
        for i in range(3):
            y = 24 + i * line_gap
            draw.line((30, y, size[0] - 30, y), fill=fg, width=6)
        ellipse_size = min(42, size[1] - 40)
        draw.ellipse((size[0] - ellipse_size - 24, size[1] - ellipse_size - 24, size[0] - 24, size[1] - 24), fill=fg)
    return image


def make_laptop_art(size: tuple[int, int]) -> Image.Image:
    image = Image.new("RGBA", size, (0, 0, 0, 0))
    draw = ImageDraw.Draw(image)
    screen = (40, 20, size[0] - 40, size[1] - 58)
    draw.rounded_rectangle(screen, radius=16, fill="#ffffff", outline="#2f3c53", width=8)
    draw.ellipse((76, 60, 136, 120), fill="#4a82ed")
    draw.text((88, 74), "W", font=FONT_15B, fill="white")
    draw.polygon([(size[0] // 2, 58), (size[0] // 2 + 28, 100), (size[0] // 2 - 28, 100)], fill="#ec5aa1")
    draw.text((size[0] - 146, 70), "NEXT", font=FONT_15B, fill="#111827")
    draw.rounded_rectangle((size[0] - 162, 54, size[0] - 52, 108), radius=20, outline="#2f3c53", width=4)
    draw.polygon([(20, size[1] - 38), (size[0] - 20, size[1] - 38), (size[0] - 70, size[1] - 8), (70, size[1] - 8)], fill="#d3dae8")
    draw.ellipse((90, size[1] - 22, size[0] - 90, size[1] + 10), fill=(135, 149, 176, 80))
    return image


def generate_home_desktop(posts: list[dict], out_path: Path):
    width, height = 1400, 1500
    img = Image.new("RGBA", (width, height), COLORS["bg"])
    draw = ImageDraw.Draw(img)
    shell_w = 980
    shell_x = (width - shell_w) // 2

    draw.rectangle((0, 0, width, 74), fill="white")
    draw.line((0, 73, width, 73), fill=(38, 50, 71, 20), width=1)
    draw.text((shell_x, 23), "Logo", font=FONT_28B, fill=COLORS["text"])
    nav_x = shell_x + 430
    for i, label in enumerate(["Home", "About", "Contact"]):
        draw.text((nav_x + i * 72, 28), label, font=FONT_14, fill=COLORS["nav"])
    draw_button(draw, (shell_x + shell_w - 126, 15), "Get Started", FONT_14, COLORS["primary"])

    hero_y = 74
    hero_h = 320
    hero = hero_background((width, hero_h))
    img.alpha_composite(hero, (0, hero_y))
    draw = ImageDraw.Draw(img)
    draw.text((shell_x, hero_y + 58), "Welcome to MyBlog", font=FONT_13, fill=(255, 255, 255, 220))
    draw_text_block(draw, shell_x, hero_y + 82, "Insights & Stories Powered by WordPress & Next.js", FONT_32B, "white", 430, line_spacing=4)
    draw_text_block(draw, shell_x, hero_y + 180, "A lightweight editorial homepage built twice from one WordPress source of truth: once with Elementor and once with WPGraphQL and Next.js.", FONT_14, (255, 255, 255, 210), 380, line_spacing=6)
    draw_button(draw, (shell_x, hero_y + 242), "Read More", FONT_14, COLORS["primary"])
    laptop = make_laptop_art((430, 250))
    img.alpha_composite(laptop, (shell_x + 530, hero_y + 34))

    posts_y = hero_y + hero_h
    draw.rectangle((0, posts_y, width, 1180), fill=COLORS["surface_alt"])
    draw.text((shell_x, posts_y + 40), "Latest Posts", font=FONT_28B, fill=COLORS["text"])
    card_w = 316
    gap = 16
    start_y = posts_y + 90
    for index, post in enumerate(posts):
        row, col = divmod(index, 3)
        x = shell_x + col * (card_w + gap)
        y = start_y + row * (242 + 18)
        draw_card(draw, img, post, x, y, card_w)

    draw.text((width // 2 - 34, 1125), "© 2025 MyBlog", font=FONT_11, fill="#9aa5b5")
    out_path.parent.mkdir(parents=True, exist_ok=True)
    img.convert("RGB").save(out_path)


def generate_home_mobile(posts: list[dict], out_path: Path):
    width = 390
    card_w = 342
    card_h = 312
    total_h = 74 + 340 + 70 + (len(posts) * (card_h + 16)) + 90
    img = Image.new("RGBA", (width, total_h), COLORS["bg"])
    draw = ImageDraw.Draw(img)
    shell_x = 24

    draw.rectangle((0, 0, width, 118), fill="white")
    draw.line((0, 117, width, 117), fill=(38, 50, 71, 20), width=1)
    draw.text((shell_x, 24), "Logo", font=FONT_28B, fill=COLORS["text"])
    draw.text((shell_x + 4, 63), "Home    About    Contact", font=FONT_14, fill=COLORS["nav"])
    draw_button(draw, (width - 24 - 126, 18), "Get Started", FONT_14, COLORS["primary"])

    hero_y = 118
    hero_h = 360
    hero = hero_background((width, hero_h))
    img.alpha_composite(hero, (0, hero_y))
    draw = ImageDraw.Draw(img)
    draw.text((shell_x, hero_y + 34), "Welcome to MyBlog", font=FONT_13, fill=(255, 255, 255, 220))
    draw_text_block(draw, shell_x, hero_y + 64, "Insights & Stories Powered by WordPress & Next.js", FONT_28B, "white", 250, line_spacing=4)
    draw_text_block(draw, shell_x, hero_y + 172, "A lightweight editorial homepage built twice from one WordPress source of truth: once with Elementor and once with WPGraphQL and Next.js.", FONT_14, (255, 255, 255, 210), 280, line_spacing=6)
    draw_button(draw, (shell_x, hero_y + 258), "Read More", FONT_14, COLORS["primary"])
    laptop = make_laptop_art((250, 145))
    img.alpha_composite(laptop, (width - 274, hero_y + 202))

    posts_y = hero_y + hero_h
    draw.rectangle((0, posts_y, width, total_h), fill=COLORS["surface_alt"])
    draw.text((shell_x, posts_y + 34), "Latest Posts", font=FONT_28B, fill=COLORS["text"])
    cursor = posts_y + 84
    for post in posts:
        draw_card(draw, img, post, shell_x, cursor, card_w, mobile=True)
        cursor += card_h + 16

    out_path.parent.mkdir(parents=True, exist_ok=True)
    img.convert("RGB").save(out_path)


def generate_post_page(post: dict, out_path: Path):
    width, height = 1400, 1500
    img = Image.new("RGBA", (width, height), "white")
    draw = ImageDraw.Draw(img)
    shell_w = 980
    shell_x = (width - shell_w) // 2

    draw.rectangle((0, 0, width, 74), fill="white")
    draw.line((0, 73, width, 73), fill=(38, 50, 71, 20), width=1)
    draw.text((shell_x, 23), "Logo", font=FONT_28B, fill=COLORS["text"])
    draw.text((shell_x + 430, 28), "Home", font=FONT_14, fill=COLORS["nav"])
    draw.text((shell_x + 502, 28), "About", font=FONT_14, fill=COLORS["nav"])
    draw.text((shell_x + 576, 28), "Contact", font=FONT_14, fill=COLORS["nav"])
    draw_button(draw, (shell_x + shell_w - 126, 15), "Get Started", FONT_14, COLORS["primary"])

    cursor = 122
    draw_text_block(draw, shell_x, cursor, post["title"], FONT_48B, COLORS["text"], 840, line_spacing=8)
    cursor = 240
    draw.text((shell_x, cursor), "John Doe", font=FONT_14, fill=COLORS["muted"])
    draw.text((shell_x + 105, cursor), format_date(post["date"]), font=FONT_14, fill=COLORS["muted"])
    cursor += 34
    feature = fetch_image(post["featuredImage"]["node"]["sourceUrl"], (980, 420))
    img.alpha_composite(feature, (shell_x, cursor))
    cursor += 450

    paragraphs = [p for p in re.split(r"\n{2,}", clean_text(post["content"])) if p]
    if not paragraphs:
        paragraphs = [clean_text(post["excerpt"])]
    for paragraph in paragraphs[:4]:
        cursor = draw_text_block(draw, shell_x, cursor, paragraph, FONT_14, COLORS["muted"], 860, line_spacing=7) + 10

    out_path.parent.mkdir(parents=True, exist_ok=True)
    img.convert("RGB").save(out_path)


def main():
    latest = post_json(LATEST_POSTS_QUERY)["data"]["posts"]["nodes"]
    single = post_json(POST_BY_SLUG_QUERY, {"slug": "getting-started-with-next-js"})["data"]["post"]
    generate_home_desktop(latest, SCREENSHOT_DIR / "next-homepage-desktop.png")
    generate_home_mobile(latest, SCREENSHOT_DIR / "next-homepage-mobile.png")
    generate_post_page(single, SCREENSHOT_DIR / "next-post-page.png")


if __name__ == "__main__":
    main()
