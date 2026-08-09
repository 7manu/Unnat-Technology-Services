from pathlib import Path

from PIL import Image, ImageDraw, ImageFilter, ImageFont


ROOT = Path(__file__).resolve().parents[2]
OUTPUT_DIR = Path(__file__).resolve().parent
LOGO_PATH = ROOT / "assets/images/uts-logo-removebg-removebg-preview-512x512.webp"

WIDTH, HEIGHT = 1125, 675  # 3.75 × 2.25 inches at 300 DPI, including bleed.
NAVY = "#071126"
NAVY_SOFT = "#10203d"
GOLD = "#f3b61f"
GOLD_BRIGHT = "#ffd84f"
CREAM = "#fff9e9"
WHITE = "#ffffff"
MUTED = "#69758d"

FONT_REGULAR = "/System/Library/Fonts/Supplemental/Arial.ttf"
FONT_BOLD = "/System/Library/Fonts/Supplemental/Arial Bold.ttf"


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    return ImageFont.truetype(FONT_BOLD if bold else FONT_REGULAR, size)


def add_soft_glow(image: Image.Image, center: tuple[int, int], radius: int, color: tuple[int, int, int, int]) -> None:
    glow = Image.new("RGBA", image.size, (0, 0, 0, 0))
    glow_draw = ImageDraw.Draw(glow)
    x, y = center
    glow_draw.ellipse((x - radius, y - radius, x + radius, y + radius), fill=color)
    glow = glow.filter(ImageFilter.GaussianBlur(radius // 2))
    image.alpha_composite(glow)


def fit_logo(max_size: int) -> Image.Image:
    logo = Image.open(LOGO_PATH).convert("RGBA")
    alpha = logo.getchannel("A")
    box = alpha.getbbox()
    if box:
        logo = logo.crop(box)
    logo.thumbnail((max_size, max_size), Image.Resampling.LANCZOS)
    return logo


def draw_front() -> Image.Image:
    image = Image.new("RGBA", (WIDTH, HEIGHT), NAVY)
    draw = ImageDraw.Draw(image)

    add_soft_glow(image, (155, 105), 250, (255, 216, 79, 50))
    add_soft_glow(image, (1050, 570), 260, (243, 182, 31, 32))
    draw = ImageDraw.Draw(image)

    draw.polygon([(925, 0), (1125, 0), (1125, 675), (1070, 675)], fill="#0b1931")
    draw.polygon([(1060, 0), (1125, 0), (1125, 675), (1110, 675)], fill=GOLD)
    draw.line((1000, 85, 1000, 585), fill=(243, 182, 31, 90), width=2)
    for y in (115, 337, 560):
        draw.ellipse((991, y - 9, 1009, y + 9), fill=GOLD_BRIGHT)

    plate = (80, 178, 375, 473)
    draw.ellipse(plate, fill=CREAM, outline=(255, 216, 79, 165), width=4)
    draw.ellipse((98, 196, 357, 455), outline=(243, 182, 31, 85), width=2)
    logo = fit_logo(225)
    image.alpha_composite(logo, (227 - logo.width // 2, 326 - logo.height // 2))

    draw.text((440, 185), "UNNAT", font=font(68, True), fill=WHITE)
    draw.rounded_rectangle((442, 273, 570, 281), radius=4, fill=GOLD)
    draw.text((440, 305), "TECHNOLOGY", font=font(35, True), fill=CREAM)
    draw.text((440, 348), "SERVICES", font=font(35, True), fill=CREAM)
    draw.text((442, 420), "SMART  •  SCALABLE  •  FUTURE-READY", font=font(20, True), fill=GOLD_BRIGHT)
    draw.text((442, 515), "unnattechnologyservices.com", font=font(28), fill=WHITE)
    draw.rounded_rectangle((442, 563, 815, 568), radius=3, fill=(255, 255, 255, 60))

    return image.convert("RGB")


def draw_service_item(draw: ImageDraw.ImageDraw, y: int, label: str) -> None:
    draw.ellipse((78, y + 7, 90, y + 19), fill=GOLD_BRIGHT)
    draw.text((106, y), label, font=font(24, True), fill=WHITE)


def draw_back() -> Image.Image:
    image = Image.new("RGBA", (WIDTH, HEIGHT), CREAM)
    draw = ImageDraw.Draw(image)

    draw.rectangle((0, 0, 372, HEIGHT), fill=NAVY)
    draw.rectangle((0, 0, 14, HEIGHT), fill=GOLD)
    add_soft_glow(image, (1120, 20), 265, (255, 216, 79, 42))
    draw = ImageDraw.Draw(image)

    logo = fit_logo(94)
    image.alpha_composite(logo, (74, 56))
    draw.text((184, 66), "UNNAT", font=font(31, True), fill=WHITE)
    draw.text((184, 103), "TECHNOLOGY SERVICES", font=font(14, True), fill=GOLD_BRIGHT)
    draw.rounded_rectangle((75, 175, 330, 179), radius=2, fill=(255, 255, 255, 50))
    draw.text((75, 213), "SERVICES", font=font(18, True), fill=GOLD_BRIGHT)
    draw_service_item(draw, 265, "Website Development")
    draw_service_item(draw, 323, "E-Commerce")
    draw_service_item(draw, 381, "Mobile Apps")
    draw_service_item(draw, 439, "Custom Applications")
    draw_service_item(draw, 497, "Technology Services")
    draw.text((75, 590), "SMART DIGITAL SOLUTIONS", font=font(14, True), fill=(255, 255, 255, 170))

    content_x = 432
    draw.rounded_rectangle((content_x, 82, content_x + 95, 90), radius=4, fill=GOLD)
    draw.text((content_x, 122), "ANSH SHARMA", font=font(54, True), fill=NAVY)
    draw.text((content_x + 2, 187), "TECHNOLOGY SOLUTIONS", font=font(22, True), fill="#b77700")
    draw.rounded_rectangle((content_x, 243, 1048, 246), radius=2, fill="#eadcb8")

    draw.text((content_x, 286), "CALL", font=font(17, True), fill="#b77700")
    draw.text((content_x, 319), "+91 74649 72155", font=font(34, True), fill=NAVY_SOFT)
    draw.text((content_x, 365), "+91 96908 05228", font=font(34, True), fill=NAVY_SOFT)

    draw.text((content_x, 445), "WEB", font=font(17, True), fill="#b77700")
    draw.text((content_x, 478), "unnattechnologyservices.com", font=font(27, True), fill=NAVY_SOFT)

    draw.rounded_rectangle((content_x, 568, 1048, 620), radius=15, fill=NAVY)
    draw.text((content_x + 23, 582), "WEB  •  MOBILE  •  SOFTWARE  •  AUTOMATION", font=font(16, True), fill=GOLD_BRIGHT)

    return image.convert("RGB")


def add_preview_card(canvas: Image.Image, card: Image.Image, xy: tuple[int, int]) -> None:
    x, y = xy
    shadow = Image.new("RGBA", canvas.size, (0, 0, 0, 0))
    shadow_draw = ImageDraw.Draw(shadow)
    shadow_draw.rounded_rectangle((x + 10, y + 16, x + 1010, y + 616), radius=28, fill=(7, 17, 38, 48))
    canvas.alpha_composite(shadow.filter(ImageFilter.GaussianBlur(18)))

    resized = card.resize((1000, 600), Image.Resampling.LANCZOS).convert("RGBA")
    mask = Image.new("L", resized.size, 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, 999, 599), radius=24, fill=255)
    resized.putalpha(mask)
    canvas.alpha_composite(resized, (x, y))


def draw_preview(front: Image.Image, back: Image.Image) -> Image.Image:
    preview = Image.new("RGBA", (1200, 1530), "#edf0f5")
    draw = ImageDraw.Draw(preview)
    draw.text((100, 58), "ANSH SHARMA — VISITING CARD", font=font(34, True), fill=NAVY)
    draw.text((100, 110), "Front", font=font(20, True), fill=MUTED)
    add_preview_card(preview, front, (100, 155))
    draw.text((100, 800), "Back", font=font(20, True), fill=MUTED)
    add_preview_card(preview, back, (100, 845))
    draw.text((100, 1480), "3.75 × 2.25 in • 300 DPI • includes print bleed", font=font(18), fill=MUTED)
    return preview.convert("RGB")


def main() -> None:
    front = draw_front()
    back = draw_back()
    preview = draw_preview(front, back)

    front_path = OUTPUT_DIR / "ansh-sharma-visiting-card-front.png"
    back_path = OUTPUT_DIR / "ansh-sharma-visiting-card-back.png"
    preview_path = OUTPUT_DIR / "ansh-sharma-visiting-card-preview.png"
    pdf_path = OUTPUT_DIR / "ansh-sharma-visiting-card-print.pdf"

    front.save(front_path, dpi=(300, 300), optimize=True)
    back.save(back_path, dpi=(300, 300), optimize=True)
    preview.save(preview_path, optimize=True)
    front.save(pdf_path, "PDF", resolution=300, save_all=True, append_images=[back])

    for path in (front_path, back_path, preview_path, pdf_path):
        print(path)


if __name__ == "__main__":
    main()
