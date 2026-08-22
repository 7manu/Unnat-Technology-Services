"""Builds the UTS admin panel employee handbook as a print-ready PDF.

Run after changing the admin panel so the guide stays accurate:

    pip3 install reportlab
    python3 docs/build_admin_guide.py
"""

import os
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import cm, mm
from reportlab.platypus import (BaseDocTemplate, Frame, KeepTogether, ListFlowable,
                                ListItem, NextPageTemplate, PageBreak, PageTemplate,
                                Paragraph, Spacer, Table, TableStyle, Image)

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, "docs", "UTS-Admin-Panel-Guide.pdf")
LOGO = os.path.join(ROOT, "assets/images/uts-logo-removebg-removebg-preview.png")

INK = colors.HexColor("#0b1733")
INK_SOFT = colors.HexColor("#41506e")
MUTED = colors.HexColor("#6b7689")
AMBER = colors.HexColor("#c2761a")
AMBER_LIGHT = colors.HexColor("#fdf3e0")
LINE = colors.HexColor("#dcd9cd")
PANEL = colors.HexColor("#f6f7fa")
GREEN = colors.HexColor("#0b6b47")
GREEN_LIGHT = colors.HexColor("#e7f5ee")
RED = colors.HexColor("#93221b")
RED_LIGHT = colors.HexColor("#fdeceb")

styles = getSampleStyleSheet()


def style(name, **kw):
    base = kw.pop("parent", styles["Normal"])
    return ParagraphStyle(name, parent=base, **kw)


S = {
    "cover_title": style("cover_title", fontName="Helvetica-Bold", fontSize=30, leading=35,
                         textColor=INK, alignment=TA_CENTER, spaceAfter=10),
    "cover_sub": style("cover_sub", fontName="Helvetica", fontSize=13, leading=19,
                       textColor=MUTED, alignment=TA_CENTER),
    "cover_meta": style("cover_meta", fontName="Helvetica", fontSize=10, leading=15,
                        textColor=MUTED, alignment=TA_CENTER),
    "h1": style("h1", fontName="Helvetica-Bold", fontSize=19, leading=23, textColor=INK,
                spaceBefore=4, spaceAfter=8),
    "h2": style("h2", fontName="Helvetica-Bold", fontSize=13.5, leading=17, textColor=INK,
                spaceBefore=14, spaceAfter=5),
    "h3": style("h3", fontName="Helvetica-Bold", fontSize=11, leading=14, textColor=AMBER,
                spaceBefore=10, spaceAfter=3),
    "body": style("body", fontName="Helvetica", fontSize=9.8, leading=14.5,
                  textColor=INK_SOFT, spaceAfter=7),
    "lead": style("lead", fontName="Helvetica", fontSize=11, leading=16.5,
                  textColor=INK_SOFT, spaceAfter=10),
    "bullet": style("bullet", fontName="Helvetica", fontSize=9.8, leading=14,
                    textColor=INK_SOFT, spaceAfter=3),
    "cell": style("cell", fontName="Helvetica", fontSize=8.6, leading=12, textColor=INK_SOFT),
    "cellb": style("cellb", fontName="Helvetica-Bold", fontSize=8.6, leading=12, textColor=INK),
    "cellhead": style("cellhead", fontName="Helvetica-Bold", fontSize=8.2, leading=11,
                      textColor=colors.white),
    "callout": style("callout", fontName="Helvetica", fontSize=9.2, leading=13.5, textColor=INK_SOFT),
    "calloutb": style("calloutb", fontName="Helvetica-Bold", fontSize=9.2, leading=13.5, textColor=INK),
    "toc": style("toc", fontName="Helvetica", fontSize=10, leading=17, textColor=INK_SOFT),
    "path": style("path", fontName="Helvetica-Bold", fontSize=9.2, leading=13, textColor=AMBER,
                  spaceAfter=6),
}


def para(text, s="body"):
    return Paragraph(text, S[s])


def bullets(items, s="bullet"):
    return ListFlowable(
        [ListItem(Paragraph(t, S[s]), leftIndent=14, value="circle") for t in items],
        bulletType="bullet", start="circle", leftIndent=12, bulletFontSize=5,
        bulletOffsetY=-2, spaceAfter=8,
    )


def numbered(items):
    return ListFlowable(
        [ListItem(Paragraph(t, S["bullet"]), leftIndent=16) for t in items],
        bulletType="1", leftIndent=14, bulletFontName="Helvetica-Bold",
        bulletFontSize=9.5, spaceAfter=8,
    )


def table(rows, widths, head=True):
    data = []
    for i, row in enumerate(rows):
        if i == 0 and head:
            data.append([Paragraph(c, S["cellhead"]) for c in row])
        else:
            data.append([Paragraph(row[0], S["cellb"])] + [Paragraph(c, S["cell"]) for c in row[1:]])

    t = Table(data, colWidths=widths, repeatRows=1 if head else 0)
    cmds = [
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
        ("LEFTPADDING", (0, 0), (-1, -1), 7),
        ("RIGHTPADDING", (0, 0), (-1, -1), 7),
        ("LINEBELOW", (0, 1 if head else 0), (-1, -1), 0.4, LINE),
    ]
    if head:
        cmds += [("BACKGROUND", (0, 0), (-1, 0), INK),
                 ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, PANEL])]
    else:
        cmds += [("ROWBACKGROUNDS", (0, 0), (-1, -1), [colors.white, PANEL])]
    t.setStyle(TableStyle(cmds))
    return t


def callout(title, text, kind="note"):
    palette = {"note": (AMBER_LIGHT, AMBER), "good": (GREEN_LIGHT, GREEN), "warn": (RED_LIGHT, RED)}
    bg, bar = palette[kind]
    inner = [Paragraph(title, S["calloutb"]), Spacer(1, 2), Paragraph(text, S["callout"])]
    t = Table([[inner]], colWidths=[16.4 * cm])
    t.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), bg),
        ("LINEBEFORE", (0, 0), (0, -1), 2.5, bar),
        ("LEFTPADDING", (0, 0), (-1, -1), 10),
        ("RIGHTPADDING", (0, 0), (-1, -1), 10),
        ("TOPPADDING", (0, 0), (-1, -1), 8),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 8),
    ]))
    return KeepTogether([Spacer(1, 4), t, Spacer(1, 8)])


def where(text):
    return Paragraph("Where to find it: " + text, S["path"])


# ---------------------------------------------------------------- page frames
def cover_page(canvas, doc):
    canvas.saveState()
    canvas.setFillColor(INK)
    canvas.rect(0, A4[1] - 1.2 * cm, A4[0], 1.2 * cm, stroke=0, fill=1)
    canvas.setFillColor(AMBER)
    canvas.rect(0, A4[1] - 1.45 * cm, A4[0], 0.25 * cm, stroke=0, fill=1)
    canvas.restoreState()


def content_page(canvas, doc):
    canvas.saveState()
    canvas.setStrokeColor(LINE)
    canvas.setLineWidth(0.5)
    canvas.line(2 * cm, A4[1] - 1.55 * cm, A4[0] - 2 * cm, A4[1] - 1.55 * cm)
    canvas.setFont("Helvetica", 7.5)
    canvas.setFillColor(MUTED)
    canvas.drawString(2 * cm, A4[1] - 1.35 * cm, "Unnat Technology Services - Admin Panel Guide")
    canvas.drawRightString(A4[0] - 2 * cm, A4[1] - 1.35 * cm, "unnattechnologyservices.com")
    canvas.line(2 * cm, 1.5 * cm, A4[0] - 2 * cm, 1.5 * cm)
    canvas.drawString(2 * cm, 1.1 * cm, "Internal document - do not share the passwords in this guide outside the team")
    canvas.drawRightString(A4[0] - 2 * cm, 1.1 * cm, "Page %d" % (doc.page - 1))
    canvas.restoreState()


doc = BaseDocTemplate(OUT, pagesize=A4, title="UTS Admin Panel Guide",
                      author="Unnat Technology Services",
                      subject="How to use the website admin panel",
                      leftMargin=2 * cm, rightMargin=2 * cm,
                      topMargin=2.1 * cm, bottomMargin=2 * cm)
frame = Frame(doc.leftMargin, doc.bottomMargin, doc.width, doc.height, id="body")
doc.addPageTemplates([
    PageTemplate(id="cover", frames=[frame], onPage=cover_page),
    PageTemplate(id="content", frames=[frame], onPage=content_page),
])

story = []
W = 16.4 * cm

# ============================================================== COVER
story.append(Spacer(1, 2.4 * cm))
if os.path.isfile(LOGO):
    try:
        img = Image(LOGO, width=3.6 * cm, height=3.6 * cm, kind="proportional")
        img.hAlign = "CENTER"
        story.append(img)
        story.append(Spacer(1, 1.1 * cm))
    except Exception:
        story.append(Spacer(1, 1 * cm))

story.append(para("UNNAT TECHNOLOGY SERVICES", "cover_meta"))
story.append(Spacer(1, 6))
story.append(para("Website Admin Panel", "cover_title"))
story.append(para("Staff Handbook", "cover_title"))
story.append(Spacer(1, 14))
story.append(para("Everything you can change on the public website, where to find it,<br/>"
                  "and the exact steps for the jobs you will do most often.", "cover_sub"))
story.append(Spacer(1, 2.2 * cm))

info = Table([
    [Paragraph("Applies to", S["cellb"]), Paragraph("unnattechnologyservices.com admin panel", S["cell"])],
    [Paragraph("Panel address", S["cellb"]), Paragraph("https://unnattechnologyservices.com/login.php", S["cell"])],
    [Paragraph("Audience", S["cellb"]), Paragraph("Content and marketing staff. No coding knowledge needed.", S["cell"])],
    [Paragraph("Version", S["cellb"]), Paragraph("1.0 - August 2026", S["cell"])],
], colWidths=[4 * cm, 10 * cm])
info.hAlign = "CENTER"
info.setStyle(TableStyle([
    ("VALIGN", (0, 0), (-1, -1), "TOP"),
    ("TOPPADDING", (0, 0), (-1, -1), 6),
    ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
    ("LEFTPADDING", (0, 0), (-1, -1), 10),
    ("LINEBELOW", (0, 0), (-1, -2), 0.4, LINE),
    ("BOX", (0, 0), (-1, -1), 0.6, LINE),
    ("BACKGROUND", (0, 0), (0, -1), PANEL),
]))
story.append(info)

story.append(NextPageTemplate("content"))
story.append(PageBreak())

# ============================================================== CONTENTS
story.append(para("What is in this guide", "h1"))
story.append(para("Read section 1 and 2 first. After that, use the contents list to jump to whatever "
                  "you need. Section 15 has step-by-step recipes for the most common jobs.", "lead"))

toc_rows = [
    ["Section", "Page covers"],
    ["1. Signing in", "The address, your login, signing out safely"],
    ["2. How the panel is laid out", "Topbar, sidebar, the four groups of tools"],
    ["3. Dashboard", "Numbers at a glance and the SEO checklist"],
    ["4. Website content", "Every word on the site - the section you will use most"],
    ["5. Pages", "Creating new pages and putting them in the menus"],
    ["6. Blog", "Writing, publishing and optimising articles"],
    ["7. Products", "Adding, editing and removing portfolio items"],
    ["8. Media library", "Uploading images and reusing them"],
    ["9. Page SEO", "Titles, descriptions and social sharing per page"],
    ["10. Keywords", "Planning the search terms each page should win"],
    ["11. Backlinks", "Recording every website that links to us"],
    ["12. Links and URLs", "Editing the header, mobile and footer menus"],
    ["13. Redirects", "Sending an old address to a new one"],
    ["14. Settings and accounts", "Site-wide options, analytics codes, staff logins"],
    ["15. Step-by-step recipes", "The ten jobs you will actually be asked to do"],
    ["16. Rules, cautions and troubleshooting", "What to avoid and what to do when stuck"],
]
story.append(table(toc_rows, [6.2 * cm, 10.2 * cm]))

story.append(PageBreak())

# ============================================================== 1. SIGNING IN
story.append(para("1. Signing in", "h1"))
story.append(para("The admin panel is a private area of the website. It is hidden from Google and "
                  "cannot be reached from any public menu, so you must type the address directly.", "lead"))

story.append(para("The address", "h2"))
story.append(where("https://unnattechnologyservices.com/login.php"))
story.append(para("Save it as a bookmark. Bookmark the login page, not the dashboard - if your session "
                  "has expired you will be sent back to the login page anyway.", "body"))

story.append(para("Your login details", "h2"))
story.append(para("You sign in with a <b>mobile number</b> and a <b>password</b>. The mobile number is "
                  "your user ID, not your email address. Ask the owner to create an account for you "
                  "rather than sharing theirs - that way the activity log shows who changed what.", "body"))

login_box = Table([
    [Paragraph("Mobile number (your ID)", S["cellb"]), Paragraph("&nbsp;", S["cell"])],
    [Paragraph("Password", S["cellb"]), Paragraph("&nbsp;", S["cell"])],
], colWidths=[6 * cm, 10.4 * cm], rowHeights=[1.05 * cm, 1.05 * cm])
login_box.setStyle(TableStyle([
    ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
    ("LEFTPADDING", (0, 0), (-1, -1), 10),
    ("BOX", (0, 0), (-1, -1), 0.6, LINE),
    ("INNERGRID", (0, 0), (-1, -1), 0.4, LINE),
    ("BACKGROUND", (0, 0), (0, -1), PANEL),
]))
story.append(login_box)
story.append(Spacer(1, 10))

story.append(callout("Change your password on day one",
                     "Go to <b>Admin accounts</b> and use <b>Change your own password</b>. Passwords must be "
                     "at least 10 characters. They are stored scrambled, so nobody - including the owner - "
                     "can read your password back. It can only be replaced.", "note"))

story.append(para("Signing out", "h2"))
story.append(para("Use the <b>Log out</b> button in the top right corner when you finish, especially on a "
                  "shared or public computer. Simply closing the tab leaves the session open.", "body"))

story.append(callout("If a change does not appear on the website",
                     "Your browser may be showing you a saved copy. Hold Shift and click reload, or open "
                     "the page in a private window. Ninety per cent of \"my edit did not save\" reports are "
                     "this.", "good"))

story.append(PageBreak())

# ============================================================== 2. LAYOUT
story.append(para("2. How the panel is laid out", "h1"))
story.append(para("Every screen has the same three parts.", "lead"))

story.append(para("The topbar", "h3"))
story.append(bullets([
    "<b>UTS Admin</b> on the left takes you back to the dashboard.",
    "Your name is shown so you can confirm which account you are using.",
    "<b>View site</b> opens the public website in a new tab - keep it open to check your work.",
    "<b>Log out</b> ends your session.",
]))

story.append(para("The sidebar", "h3"))
story.append(para("On a phone or a narrow window the sidebar is hidden behind the menu button in the "
                  "top left corner. The tools are grouped into four sets:", "body"))

story.append(table([
    ["Group", "Contains", "Use it for"],
    ["Overview", "Dashboard, Inquiries, Activity log",
     "Checking the state of things and reading customer enquiries"],
    ["Content", "Website content, Pages, Blog, Products, Media library",
     "Anything a visitor reads or looks at"],
    ["Search engine optimisation", "Page SEO, Keywords, Backlinks, Links and URLs, Redirects",
     "Everything that affects how Google finds and shows the site"],
    ["Configuration", "SEO and site settings, Admin accounts",
     "Site-wide options and staff logins"],
], [3.6 * cm, 6.2 * cm, 6.6 * cm]))

story.append(para("The working area", "h3"))
story.append(para("The rest of the screen is the tool you selected. A coloured strip appears at the top "
                  "after you save: <b>green</b> means the change was saved, <b>red</b> means something was "
                  "rejected and nothing was saved. Always read the red message - it tells you which "
                  "field was wrong.", "body"))

story.append(callout("Changes are live immediately",
                     "There is no separate publish step for website text. The moment you press a save "
                     "button, visitors see the new wording. The only things with a draft state are "
                     "<b>Pages</b> and <b>Blog</b> articles, which stay hidden until you set their status "
                     "to Published.", "warn"))

story.append(PageBreak())

# ============================================================== 3. DASHBOARD
story.append(para("3. Dashboard", "h1"))
story.append(where("Overview -> Dashboard"))
story.append(para("The first screen after signing in. It answers \"what needs my attention?\" in three "
                  "blocks.", "lead"))

story.append(para("Counters", "h3"))
story.append(para("Total and pending enquiries, products, editable content fields, pages and articles "
                  "published, tracked keywords and live backlinks. If pending enquiries is not zero, "
                  "somebody is waiting for a reply.", "body"))

story.append(para("Quick actions", "h3"))
story.append(para("Shortcut buttons to the jobs done most often, plus links that open the live "
                  "sitemap.xml and robots.txt so you can confirm they are working.", "body"))

story.append(para("SEO readiness", "h3"))
story.append(para("A checklist of the settings Google looks for. Anything marked <b>Not set</b> has a "
                  "link straight to the screen that fixes it. Aim to have every row say Configured.", "body"))

story.append(para("Recent changes", "h3"))
story.append(para("The last eight edits made in the panel, with who made them. The full history is under "
                  "Activity log.", "body"))

story.append(callout("The re-check button",
                     "<b>Re-check database and content keys</b> at the top right rebuilds anything missing "
                     "in the database after the website code has been updated. It is safe to press at any "
                     "time and it never overwrites wording you have edited. Press it if new fields the "
                     "developer told you about are not showing up.", "note"))

story.append(PageBreak())

# ============================================================== 4. WEBSITE CONTENT
story.append(para("4. Website content", "h1"))
story.append(where("Content -> Website content"))
story.append(para("This is the heart of the panel and the section you will use most. Every single word, "
                  "link, icon and image on the public website is listed here as an editable field - "
                  "roughly 293 of them. Nothing on the site is hard-coded.", "lead"))

story.append(para("How the fields are organised", "h2"))
story.append(para("Fields are grouped by <b>page</b> first, then by the <b>section</b> of that page they "
                  "appear in. Use the tabs across the top to switch page:", "body"))

story.append(table([
    ["Tab", "What it covers"],
    ["Global", "Company name, logo, phone, email, address, social profile links. Used everywhere."],
    ["Header", "The top bar - brand text, menu labels for screen readers, skip link."],
    ["Home page", "Hero, trust strip, introduction, services, expertise, why us, process, "
                  "selected work, industries, statistics, trust promises, closing call to action, contact block."],
    ["Shared blocks", "The call/email/visit cards and the enquiry form labels, used on more than one page."],
    ["Contact page", "Hero, form block, and the three \"what happens next\" cards."],
    ["Products page", "Hero, list headings, card labels, and the two empty-state messages."],
    ["Blog", "Blog hero, article list labels, and the single-article page labels."],
    ["Footer", "Footer description, the three column headings, copyright and the closing note."],
    ["System pages", "The 404 page and the messages shown after the enquiry form is submitted."],
], [3.4 * cm, 13 * cm]))

story.append(para("Understanding the field key", "h2"))
story.append(para("Under each field name there is a grey code such as "
                  "<b>home.hero.headline_prefix</b>. It is read left to right and tells you exactly where "
                  "on the website that text appears:", "body"))

story.append(table([
    ["Part", "Meaning", "Example"],
    ["First part", "Which page", "home, contact, products, blog, footer, global"],
    ["Second part", "Which section of that page", "hero, services, process, bottom"],
    ["Third part", "Which element in that section", "headline_prefix, copy, button_label, cta_url"],
], [3 * cm, 6.4 * cm, 7 * cm]))

story.append(para("So <b>footer.bottom.copyright</b> is the copyright line in the bottom bar of the "
                  "footer, and <b>home.services.card_3_title</b> is the title of the third service card "
                  "on the home page. You never need to type these codes - they are there so you can "
                  "describe a field precisely when asking for help.", "body"))

story.append(para("Finding a field fast", "h2"))
story.append(para("Do not scroll. Use the search box above the fields. It matches the field name, the "
                  "key and the <b>current wording</b>, so the quickest way to find something is to type a "
                  "few words you can see on the live website. Searching hides everything that does not "
                  "match and opens the sections that do.", "body"))

story.append(para("Saving", "h2"))
story.append(numbered([
    "Pick the page tab you want to work on.",
    "Edit as many fields as you like across every section of that tab.",
    "Press <b>Save all changes in [tab name]</b> in the bar that stays at the bottom of the screen.",
]))
story.append(para("The save button only saves the tab you are on. If you edit Home page fields, save, "
                  "then move to Footer, your home page changes are already safe.", "body"))

story.append(callout("Fields that hold links",
                     "Some fields are addresses rather than words - their names end in _url. You can put "
                     "a full address (https://example.com), a page on our own site (products.php), or a "
                     "jump to a section of the current page (#contact). If you type something invalid the "
                     "link quietly falls back to # so the page never breaks.", "note"))

story.append(para("Adding your own field", "h2"))
story.append(para("<b>Add a custom content field</b> at the bottom of the screen creates a new editable "
                  "piece of text. This is only useful when a developer is going to place it on the site "
                  "for you - creating one on its own does not make it appear anywhere. Custom fields can "
                  "be deleted; the 293 built-in fields cannot, so the website can never lose its wording.", "body"))

story.append(PageBreak())

# ============================================================== 5. PAGES
story.append(para("5. Pages", "h1"))
story.append(where("Content -> Pages"))
story.append(para("For building new pages such as an About Us, a service detail page or a campaign "
                  "landing page. Every page uses the same template, so you only fill in a form.", "lead"))

story.append(para("The four fields a page needs", "h2"))
story.append(table([
    ["Field", "What to put in it"],
    ["Page title", "The main heading. Also used as the browser tab title unless you set a meta title."],
    ["URL slug", "The address, in lower case with hyphens - for example about-our-team. It fills in "
                 "automatically from the title; change it only if you have a reason."],
    ["Eyebrow / subtitle", "The small line above the heading. Optional."],
    ["Cover image", "Upload a file, or paste a path from the media library. Optional."],
    ["Short description", "One or two sentences below the title. Also used as the Google description "
                          "if you leave the SEO section empty."],
    ["Opening text", "Optional. Plain text shown before the sections. Leave it empty and build "
                     "the whole page from section blocks instead."],
], [4 * cm, 12.4 * cm]))

story.append(para("Writing in the opening text box", "h2"))
story.append(para("The opening text box and the Text block both accept simple HTML tags. The tags you "
                  "need:", "body"))

story.append(table([
    ["To get", "Type"],
    ["A section heading", "&lt;h2&gt;Your heading&lt;/h2&gt;"],
    ["A smaller heading", "&lt;h3&gt;Your heading&lt;/h3&gt;"],
    ["A paragraph", "&lt;p&gt;Your text&lt;/p&gt;"],
    ["A bullet list", "&lt;ul&gt;&lt;li&gt;First point&lt;/li&gt;&lt;li&gt;Second point&lt;/li&gt;&lt;/ul&gt;"],
    ["A link", "&lt;a href=\"contact.html\"&gt;Contact us&lt;/a&gt;"],
    ["An image", "&lt;img src=\"assets/uploads/photo.webp\" alt=\"Description\"&gt;"],
], [5 * cm, 11.4 * cm]))

story.append(para("Styling is applied automatically to match the rest of the site - do not try to set "
                  "colours or fonts. Scripts and embedded frames are stripped out for safety when you "
                  "save.", "body"))

story.append(para("Building the page from sections", "h2"))
story.append(para("Under <b>Page sections</b> you build the page from ready-made blocks. Press a block "
                  "name to add it, fill in its fields, and use the arrow buttons to move it up or down. "
                  "Every block that shows a picture has its own image field with a <b>Choose image</b> "
                  "button that opens the media library.", "body"))

story.append(table([
    ["Block", "What it puts on the page"],
    ["Text", "A heading and a paragraph of formatted text, left aligned or centred."],
    ["Image + text", "A picture beside a paragraph, with the image on the left or the right, "
                     "and an optional button."],
    ["Banner", "A wide image with a heading over it. Good as a page opener."],
    ["Card grid", "Two to four columns of cards. Each card has its own image or icon, title, "
                  "text and link."],
    ["Image gallery", "A grid of pictures with optional captions."],
    ["Statistics", "A row of numbers that count up as the visitor scrolls."],
    ["Questions and answers", "An expandable list. It also produces FAQ data for Google, which can "
                              "show your questions directly in search results."],
    ["Quote", "A pulled-out quote or client statement, with an optional photo."],
    ["Logo strip", "A row of client or partner logos."],
    ["Video", "A YouTube video. Paste the link or just the video ID."],
    ["Call to action", "A panel that asks the visitor to do something, in a soft or dark style."],
    ["Divider / spacing", "Blank space, with or without a line."],
], [4.2 * cm, 12.2 * cm]))

story.append(para("Every block also has a <b>Background</b> setting - plain, a soft tint, or dark. "
                  "Alternating plain and tinted backgrounds down a page is the easiest way to make a "
                  "long page readable.", "body"))

story.append(callout("Blocks are safer than typing HTML",
                     "The section blocks produce styling that matches the rest of the site "
                     "automatically. Use them in preference to the opening text box whenever you can - "
                     "you cannot break the layout with a block.", "good"))

story.append(para("Choosing where the page appears in the menus", "h2"))
story.append(para("The <b>Menu placement</b> box has three independent tick boxes. Tick any combination:", "body"))

story.append(table([
    ["Tick box", "Effect"],
    ["Show in the header menu (desktop)", "The link appears in the horizontal menu across the top on "
                                          "laptops and desktops."],
    ["Show in the mobile menu bar", "The link appears in the drawer that slides out from the menu "
                                    "button on phones and tablets."],
    ["Show in the footer", "The link appears in the footer. Use the <b>Footer column</b> dropdown "
                           "beside it to choose which of the three columns."],
], [5.6 * cm, 10.8 * cm]))

story.append(para("Ticking header only hides the link on phones. Ticking mobile only hides it on "
                  "desktop. Tick both to show it everywhere - that is what you normally want. "
                  "<b>Menu position</b> controls the order; lower numbers come first. The menu links are "
                  "created, renamed and removed for you automatically as you change these boxes.", "body"))

story.append(para("Publishing", "h2"))
story.append(para("A new page starts as a <b>Draft</b> and is invisible to visitors. Set <b>Status</b> to "
                  "Published when it is ready. You can keep a page in draft for as long as you like while "
                  "you work on it.", "body"))

story.append(para("Layout templates", "h3"))
story.append(bullets([
    "<b>Standard</b> - title bar, cover image, then the content in a comfortable reading width. Use this unless you have a reason not to.",
    "<b>Wide</b> - full-width content with the cover image running edge to edge. Good for pages with tables or many images.",
    "<b>Landing</b> - cover image plus an automatic \"start a conversation\" block at the bottom. Good for campaign pages.",
    "<b>Blank canvas</b> - no title bar and no cover at all, so your first section block becomes the top of the page. Use it with a Banner block for a fully designed page.",
]))

story.append(para("The SEO section of the page form is explained in section 9 - the fields are the "
                  "same. Leave them empty and the page title and description are used automatically, "
                  "which is fine for most pages.", "body"))

story.append(PageBreak())

# ============================================================== 6. BLOG
story.append(para("6. Blog", "h1"))
story.append(where("Content -> Blog"))
story.append(para("Publishing articles is the single most effective thing you can do to bring new "
                  "visitors from Google. Articles appear on the blog page, on the home page, and are "
                  "added to the sitemap automatically.", "lead"))

story.append(para("Writing an article", "h2"))
story.append(table([
    ["Field", "Guidance"],
    ["Title", "Write for a human, not a search engine. Aim for 50 to 60 characters."],
    ["URL slug", "Fills in from the title. Once an article is published, avoid changing it - see the warning below."],
    ["Category", "One or two words, for example Automation or Web platforms. Shown as a tag on the card."],
    ["Author", "Defaults to Unnat Technology Services. Change it to a person's name if you prefer."],
    ["Tags", "Comma separated. Shown at the bottom of the article and used as keywords."],
    ["Cover image", "Strongly recommended - it is what people see on the card and when the link is shared. "
                    "A wide image around 1200 by 750 pixels works best."],
    ["Summary", "Two lines that appear on the article card and become the Google description."],
    ["Article body", "Same simple HTML tags as a page. Break it up with &lt;h2&gt; headings every few paragraphs."],
], [3.4 * cm, 13 * cm]))

story.append(para("Publishing", "h2"))
story.append(para("Set <b>Status</b> to Published and press save. Leave the publish date empty and it "
                  "records the moment you published. Reading time is calculated for you when you save. "
                  "The view counter on the list tells you how many times each article has been opened.", "body"))

story.append(callout("Do not change the slug of a published article",
                     "The slug is part of the article's web address. If you change it, every existing "
                     "link to that article - from Google, WhatsApp, or another website - stops working. "
                     "If you genuinely must change it, add a redirect from the old address to the new one "
                     "straight afterwards (section 13).", "warn"))

story.append(para("What visitors see", "h3"))
story.append(para("The blog page shows every published article as a card with its cover image, category, "
                  "date, reading time, summary and a <b>Read full article</b> button. The three newest "
                  "articles also appear on the home page. The blog is linked from the header menu and "
                  "the footer.", "body"))

story.append(PageBreak())

# ============================================================== 7. PRODUCTS
story.append(para("7. Products", "h1"))
story.append(where("Content -> Products"))
story.append(para("The portfolio items shown on the public Products page. Each entry is a name, a short "
                  "description, a link and a picture.", "lead"))

story.append(para("Adding a product", "h2"))
story.append(numbered([
    "Fill in the name, the full web address of the product, and a short description.",
    "Choose an image file.",
    "Press <b>Add product</b>.",
]))

story.append(para("Editing a product", "h2"))
story.append(numbered([
    "Press <b>Edit</b> on the row you want to change. The product loads into the form above and its row is highlighted.",
    "Change whatever you need.",
    "Leave the image field empty to keep the current picture, or choose a new file to replace it.",
    "Press <b>Save product</b>. Press <b>Cancel</b> to abandon the edit.",
]))

story.append(para("Field limits", "h2"))
story.append(table([
    ["Field", "Limit"],
    ["Product name", "25 characters"],
    ["Product URL", "50 characters - must be a full address starting with https://"],
    ["Short description", "50 characters"],
    ["Product image", "JPG, PNG, WebP or AVIF, up to 3 MB"],
], [5 * cm, 11.4 * cm]))

story.append(para("These limits come from the original database design. If the text you want does not "
                  "fit, shorten it - do not try to work around it.", "body"))

story.append(callout("Deleting is permanent",
                     "<b>Delete</b> removes the product and its image file immediately. There is no undo "
                     "and no recycle bin. If you are unsure, ask first.", "warn"))

story.append(para("The headings and button labels around the products - \"Ideas made useful\", \"Visit "
                  "product\" and so on - are not here. They live in Website content under the Products "
                  "page tab.", "body"))

story.append(PageBreak())

# ============================================================== 8. MEDIA
story.append(para("8. Media library", "h1"))
story.append(where("Content -> Media library"))
story.append(para("Upload an image once here, then reuse its path anywhere in the panel.", "lead"))

story.append(para("Uploading", "h2"))
story.append(numbered([
    "Choose the image file.",
    "Write <b>alt text</b> - a short plain description of what is in the picture. Screen readers "
    "read it aloud and Google uses it to understand the image. Do not skip it.",
    "Press <b>Upload</b>.",
]))

story.append(para("Using an uploaded image", "h2"))
story.append(para("Each image in the grid shows its path, for example "
                  "<b>assets/uploads/team-photo-a1b2c3.webp</b>. Copy that path and paste it into any "
                  "image field - a page cover, an article cover, a social share image, or an image field "
                  "in Website content.", "body"))

story.append(table([
    ["Rule", "Detail"],
    ["Accepted formats", "JPG, PNG, WebP, AVIF, GIF, SVG"],
    ["Maximum size", "5 MB per file"],
    ["Best format", "WebP - same quality at a much smaller size, so pages load faster"],
    ["Good cover size", "About 1200 by 750 pixels"],
], [4.4 * cm, 12 * cm]))

story.append(callout("Deleting an image does not remove it from pages",
                     "If you delete an image that a page or article is still using, that page will show a "
                     "broken picture. Check where an image is used before deleting it.", "warn"))

story.append(PageBreak())

# ============================================================== 9. PAGE SEO
story.append(para("9. Page SEO", "h1"))
story.append(where("Search engine optimisation -> Page SEO"))
story.append(para("Controls how each page appears in Google results and how it looks when someone "
                  "shares the link on WhatsApp, LinkedIn or Facebook. Use the tabs to pick the page.", "lead"))

story.append(para("Search result appearance", "h2"))
story.append(table([
    ["Field", "What it does", "Good practice"],
    ["Meta title", "The blue clickable line in Google", "50 to 60 characters. Put the important words first. "
                                                        "A counter under the box warns you when it is too long."],
    ["Meta description", "The grey text under the title", "140 to 160 characters. Write it as an invitation, "
                                                          "not a summary. It does not affect ranking but it does affect clicks."],
    ["Keywords", "The terms this page targets", "Comma separated. Plan them in the Keywords screen first."],
    ["Canonical URL", "The official address of this page", "Leave empty. Only fill it in if a developer asks you to."],
], [3.4 * cm, 5 * cm, 8 * cm]))

story.append(para("Crawling and indexing", "h2"))
story.append(table([
    ["Setting", "Meaning"],
    ["Search indexing: index", "Google may show this page in results. This is what you normally want."],
    ["Search indexing: noindex", "Google is asked to hide this page from results. Use only for pages "
                                 "that genuinely should not be found, such as a private thank-you page."],
    ["Link following: follow", "Links on this page pass value to the pages they point to. Normal setting."],
    ["Link following: nofollow", "Links pass no value. Rarely needed."],
    ["Include in sitemap.xml", "Tick to list this page in the file Google reads to discover pages."],
    ["Sitemap priority", "A hint from 0.3 to 1.0 about relative importance. The home page is 1.0."],
    ["Change frequency", "A hint about how often the page changes. Monthly is a safe default."],
], [4.6 * cm, 11.8 * cm]))

story.append(para("Social sharing cards", "h2"))
story.append(para("These decide the picture and text shown when the link is pasted into WhatsApp or "
                  "LinkedIn. Leave them empty and the meta title, meta description and the site's default "
                  "share image are used - which is usually correct. Set them when you want a different "
                  "picture or shorter wording for social media.", "body"))

story.append(callout("Structured data and custom head tags",
                     "The last card on this screen holds JSON-LD structured data and extra tags for the "
                     "page head. These are developer tools. Leave them empty unless you have been given "
                     "exact code to paste. Invalid content is ignored rather than printed, so a mistake "
                     "cannot break the page - but it will not help either.", "note"))

story.append(para("Pages and articles you create yourself do not appear as tabs here - they carry their "
                  "own identical SEO fields inside their own editor.", "body"))

story.append(PageBreak())

# ============================================================== 10. KEYWORDS
story.append(para("10. Keywords", "h1"))
story.append(where("Search engine optimisation -> Keywords"))
story.append(para("A planning tool. It records which search terms each page is trying to win and where "
                  "we currently rank. It does not change the website by itself - it tells you what to "
                  "write.", "lead"))

story.append(table([
    ["Field", "What to enter"],
    ["Keyword or phrase", "Exactly what a customer would type into Google, for example "
                          "\"software development company in Moradabad\"."],
    ["Target page", "The one page that should rank for it. Never target the same phrase with two pages - "
                    "they compete with each other."],
    ["Search intent", "Why someone searches it: informational, commercial, transactional, navigational or local."],
    ["Priority", "High, medium or low - your judgement of how much it matters to the business."],
    ["Status", "Tracking, target, ranking or paused."],
    ["Monthly searches", "From a keyword tool if you have one. Leave 0 if you do not know."],
    ["Difficulty", "0 to 100. Leave 0 if unknown."],
    ["Current rank", "Our present position. 0 means not ranking yet. Update it when you check."],
    ["Notes", "Anything useful - competitor pages, ideas for the article."],
], [3.6 * cm, 12.8 * cm]))

story.append(para("How to use it", "h2"))
story.append(numbered([
    "Add the terms customers actually use. Start with five.",
    "Assign each one to a single page.",
    "Copy the terms for a page into that page's <b>Keywords</b> field in Page SEO.",
    "Make sure the wording on the page genuinely uses those terms - in the heading and the first paragraph.",
    "Check your ranking every month and update the Current rank column.",
]))

story.append(callout("Start local",
                     "Broad terms like \"software company\" are extremely hard to win. Terms with a place "
                     "or a specialism - \"web development company in Moradabad\", \"school management "
                     "software India\" - are far easier and bring people who are actually likely to "
                     "become customers.", "good"))

story.append(PageBreak())

# ============================================================== 11. BACKLINKS
story.append(para("11. Backlinks", "h1"))
story.append(where("Search engine optimisation -> Backlinks"))
story.append(para("A backlink is any other website linking to ours. Google treats them as votes of "
                  "confidence, so they are one of the strongest ranking factors. This screen is the "
                  "register of every link we have earned, requested or lost.", "lead"))

story.append(table([
    ["Field", "What to enter"],
    ["Page that links to you", "The full address of the page the link sits on - not the home page of that site."],
    ["Page it links to", "Which of our pages it points at."],
    ["Anchor text", "The words that were made clickable."],
    ["Link type", "Dofollow passes ranking value. Nofollow, UGC and sponsored do not, but are still "
                  "worth having for traffic and credibility."],
    ["Placement", "Directory, guest post, blog mention, press, social profile, partner site, local "
                  "citation, forum or other."],
    ["Domain authority", "0 to 100 if you know it. Higher is more valuable."],
    ["Status", "Live, pending, lost or rejected."],
    ["Acquired on / Last checked", "Dates. Re-check live links every few months - sites remove them without telling you."],
    ["Notes", "Contact person, cost, renewal date, or what you promised in exchange."],
], [4 * cm, 12.4 * cm]))

story.append(para("The four counters at the top show live backlinks, live dofollow links, referring "
                  "domains and total records. <b>Referring domains</b> is the number that matters most - "
                  "twenty links from twenty different websites are worth far more than twenty links from "
                  "one.", "body"))

story.append(callout("Where to start",
                     "Google Business Profile, Justdial, IndiaMART, Sulekha, Clutch, and our own LinkedIn "
                     "company page. These are free, legitimate and quick. Never buy links from anyone "
                     "promising hundreds of backlinks - Google penalises it.", "good"))

story.append(PageBreak())

# ============================================================== 12. LINKS
story.append(para("12. Links and URLs", "h1"))
story.append(where("Search engine optimisation -> Links and URLs"))
story.append(para("Every menu link on the website, in one place. Use this to add a link, change where an "
                  "existing one points, reorder them or remove one.", "lead"))

story.append(para("The four menus", "h2"))
story.append(table([
    ["Menu", "Where it appears"],
    ["Main navigation", "The top bar on desktop and the slide-out drawer on phones"],
    ["Footer - column 1 (Explore)", "First footer column"],
    ["Footer - column 2 (Platforms)", "Second footer column"],
    ["Footer - column 3 (Contact)", "Third footer column"],
], [5.6 * cm, 10.8 * cm]))

story.append(para("The fields", "h2"))
story.append(table([
    ["Field", "What it does"],
    ["Link text", "The words the visitor sees."],
    ["Destination URL", "Where it goes. A page on our site (products.php), a section of a page "
                        "(index.php#services), another website (https://...), a phone number (tel:+91...) "
                        "or an email (mailto:...)."],
    ["Position", "Order within its menu. Lower numbers appear first."],
    ["Opens in", "Same tab for our own pages, new tab for other websites."],
    ["rel attribute", "Leave empty unless told otherwise. New-tab links get noopener automatically."],
    ["Shown on", "Header menu and mobile menu bar (normal), header only, or mobile only."],
    ["Style as a button", "Makes the link a solid button. Use for one call to action only."],
    ["Visible on the website", "Untick to hide a link without deleting it - useful for seasonal links."],
], [4 * cm, 12.4 * cm]))

story.append(callout("Links created by a page",
                     "If a link was created by ticking a menu box in the Pages screen, editing it in "
                     "either place works - they stay in step. Rows marked \"built-in default\" are "
                     "shipped defaults that only appear because the menu has never been customised; edit "
                     "any link once and the whole menu becomes editable.", "note"))

story.append(PageBreak())

# ============================================================== 13. REDIRECTS
story.append(para("13. Redirects", "h1"))
story.append(where("Search engine optimisation -> Redirects"))
story.append(para("A redirect automatically sends anyone who opens an old address to a new one. Without "
                  "it they get a \"page not found\" error and we lose both the visitor and the Google "
                  "ranking that address had earned.", "lead"))

story.append(para("When to add one", "h2"))
story.append(bullets([
    "You changed the slug of a page or an article.",
    "You deleted a page that used to exist.",
    "You are replacing one page with another that covers the same subject.",
]))

story.append(table([
    ["Field", "What to enter"],
    ["Old path on this site", "The part after the domain name, starting with a slash - for example /old-page.html"],
    ["New destination", "Where it should go: /products.php, or a full address on another site."],
    ["Redirect type", "301 permanent - passes the ranking to the new address. Use this almost always."],
    ["", "302 / 307 temporary - use when the move is genuinely short-term."],
    ["", "410 gone - tells Google the page was deleted deliberately and will not come back."],
    ["Active", "Untick to pause a redirect without deleting it."],
], [4 * cm, 12.4 * cm]))

story.append(para("The <b>Hits</b> column counts how many times each redirect has been used. A high "
                  "number means people are still reaching the old address, so the redirect is earning "
                  "its keep.", "body"))

story.append(PageBreak())

# ============================================================== 14. SETTINGS
story.append(para("14. Settings and accounts", "h1"))
story.append(where("Configuration -> SEO and site settings"))
story.append(para("Site-wide options, spread across six tabs. These affect every page at once, so change "
                  "them deliberately.", "lead"))

story.append(table([
    ["Tab", "Contains", "How often you touch it"],
    ["Site identity", "Website address, site name, locale, the suffix added to page titles",
     "Almost never"],
    ["Default SEO", "Fallback meta title, description and keywords, default social share image, "
                    "default indexing rules", "Occasionally"],
    ["Verification and analytics", "Google Search Console, Bing, Yandex, Pinterest and Meta "
                                   "verification codes; Google Analytics 4, Tag Manager, Meta Pixel, "
                                   "Clarity and Hotjar IDs; boxes for custom code in the page head and body",
     "When adding a new tool"],
    ["Structured data", "The organisation details Google shows in the knowledge panel - business type, "
                        "price range, opening hours, coordinates, area served", "Rarely"],
    ["Sitemap and robots", "What goes into sitemap.xml, default priority and frequency, and the full "
                           "text of robots.txt", "Rarely"],
    ["Blog and pages", "Turn the blog on or off, articles per page, default author name, default page template",
     "Rarely"],
], [3.4 * cm, 8.6 * cm, 4.4 * cm]))

story.append(para("Adding a tracking code", "h2"))
story.append(para("For Google Analytics, Tag Manager, Meta Pixel, Clarity and Hotjar you paste only the "
                  "<b>ID</b> - for example G-ABC123XYZ - into its own box. The panel writes the script "
                  "for you. Do not paste the whole script into the custom code box as well, or it will "
                  "load twice and your numbers will be wrong.", "body"))

story.append(para("The three custom code boxes are for anything with no dedicated box: code in the "
                  "<b>head</b>, code immediately after the <b>body opens</b> (where the Tag Manager "
                  "noscript tag belongs), and code just before the <b>body closes</b>. Whatever you paste "
                  "is added to every public page exactly as typed.", "body"))

story.append(callout("Custom code is powerful and unchecked",
                     "Anything in those three boxes runs on every page of the live website. A mistake can "
                     "break the whole site. Only paste code you were given by a service you trust, and "
                     "check the home page immediately afterwards.", "warn"))

story.append(para("Admin accounts", "h2"))
story.append(where("Configuration -> Admin accounts"))
story.append(bullets([
    "<b>Change your own password</b> - needs your current password and the new one twice. Minimum 10 characters.",
    "<b>Add an account</b> - name, mobile number (this becomes their login ID), password, and a role.",
    "<b>Owner</b> has full access. <b>Editor</b> is for content staff.",
    "Untick <b>Account can sign in</b> to suspend somebody without deleting their history.",
    "You cannot delete the account you are currently signed in with.",
]))

story.append(para("Give every person their own account. Shared logins make the activity log useless and "
                  "mean the password has to change every time somebody leaves.", "body"))

story.append(para("Inquiries", "h2"))
story.append(where("Overview -> Inquiries"))
story.append(para("Everything submitted through the website contact form and the AI assistant, newest "
                  "first. The <b>Source</b> column tells you which. Phone numbers and email addresses are "
                  "clickable. Press <b>Mark done</b> once you have replied so the pending count on the "
                  "dashboard stays meaningful. <b>Delete</b> is permanent.", "body"))

story.append(para("Activity log", "h2"))
story.append(where("Overview -> Activity log"))
story.append(para("The last 200 changes made in the panel - when, who, what action, and which item. Use "
                  "it to find out who changed something and when.", "body"))

story.append(PageBreak())

# ============================================================== 15. RECIPES
story.append(para("15. Step-by-step recipes", "h1"))
story.append(para("The jobs you will actually be asked to do.", "lead"))

recipes = [
    ("Change the phone number everywhere on the site",
     ["Content -> Website content, <b>Global</b> tab.",
      "In the Contact details section edit <b>Phone number (displayed)</b> - this is the version people read.",
      "Edit <b>Phone number (tel: link)</b> too - this is what dials when tapped. No spaces, with country code.",
      "If the WhatsApp number changed, edit <b>WhatsApp number</b> as well - digits only, no plus sign.",
      "Press <b>Save all changes in Global</b>.",
      "Check the footer of the live site and tap the number on a phone."]),

    ("Change the big headline on the home page",
     ["Content -> Website content, <b>Home page</b> tab.",
      "In the Hero section you will see the headline split into three fields: prefix, highlighted words, and suffix. "
      "The highlighted part is the one shown in colour.",
      "Edit the parts you want, keeping the total to a similar length.",
      "Also update <b>Headline screen-reader text</b> to the full sentence in plain words - blind visitors hear this one.",
      "Save."]),

    ("Publish a blog article",
     ["Content -> Blog, then <b>+ New article</b>.",
      "Fill in title, category, tags and summary.",
      "Upload a cover image around 1200 by 750 pixels.",
      "Write the body using &lt;h2&gt; headings and &lt;p&gt; paragraphs.",
      "Set Status to <b>Published</b> and save.",
      "Open the article from the list to check how it reads.",
      "Optional: add its target phrase in Keywords and set a meta description."]),

    ("Create a new page and put it in the menu",
     ["Content -> Pages, then <b>+ New page</b>.",
      "Fill in title, description and body. Check the slug looks sensible.",
      "In Menu placement tick <b>header menu</b> and <b>mobile menu bar</b> - and the footer if you want it there too.",
      "Set the menu position number to control where it sits.",
      "Set Status to <b>Published</b> and save.",
      "Open the site and confirm the link appears - check on a phone as well as a laptop."]),

    ("Add a new product to the portfolio",
     ["Content -> Products.",
      "Fill in name (25 characters), full URL (50), description (50).",
      "Choose an image under 3 MB.",
      "Press <b>Add product</b> and check the public Products page."]),

    ("Fix a typo somewhere on the site",
     ["Content -> Website content.",
      "Pick the tab for the page it is on - or any tab, then search.",
      "Type a few words of the wrong text into the search box.",
      "Correct the field it finds and save that tab."]),

    ("Improve how a page looks in Google",
     ["Search engine optimisation -> Page SEO, pick the page tab.",
      "Write a meta title of 50 to 60 characters with the main phrase near the front.",
      "Write a meta description of 140 to 160 characters that gives a reason to click.",
      "Paste the target phrases into Keywords.",
      "Save, then check the same phrases actually appear in the page's real wording."]),

    ("Connect Google Analytics",
     ["Get the measurement ID from Google Analytics - it looks like G-ABC123XYZ.",
      "Configuration -> SEO and site settings -> <b>Verification and analytics</b>.",
      "Paste it into <b>Google Analytics 4 measurement ID</b> and save.",
      "Open the live site in another tab and confirm Analytics shows one active user."]),

    ("Record a new backlink",
     ["Search engine optimisation -> Backlinks.",
      "Paste the full address of the page that links to us.",
      "Choose which of our pages it points at and type the anchor text.",
      "Set link type, placement and today's date.",
      "Save. Re-check it in a few months and update the status if it has gone."]),

    ("Retire a page safely",
     ["Content -> Pages. Set its status to Draft, or delete it if it will never return.",
      "Search engine optimisation -> Redirects.",
      "Add a redirect from the old path to the closest replacement page, type 301.",
      "Open the old address in a browser and confirm you land on the new page."]),
]

for i, (title, steps) in enumerate(recipes, 1):
    block = [Paragraph("%d. %s" % (i, title), S["h3"]), numbered(steps)]
    story.append(KeepTogether(block))

story.append(PageBreak())

# ============================================================== 16. RULES
story.append(para("16. Rules, cautions and troubleshooting", "h1"))

story.append(para("Always", "h2"))
story.append(bullets([
    "Check your change on the live site straight afterwards - on a phone as well as a computer.",
    "Write alt text for every image you upload.",
    "Add a redirect whenever you change or remove an address.",
    "Read the red message if a save is rejected. It names the field that was wrong.",
    "Use your own account, never somebody else's.",
]))

story.append(para("Never", "h2"))
story.append(bullets([
    "Never paste code you do not understand into the custom code boxes.",
    "Never set the home page to <b>noindex</b>. It removes the whole site from Google.",
    "Never change the slug of a published page or article without adding a redirect.",
    "Never delete a product, page, article or image unless you are certain - there is no undo.",
    "Never share the admin address and password over WhatsApp or email in the same message.",
]))

story.append(para("Troubleshooting", "h2"))
story.append(table([
    ["Problem", "What to do"],
    ["My change is not showing on the website",
     "Hold Shift and reload the page, or open it in a private window. If it still shows the old text, "
     "check you pressed the save button for the correct tab."],
    ["A red message says the action could not be verified",
     "Your session expired while the form was open. Sign in again and redo the change."],
    ["A red message says some fields were missing or invalid",
     "Nothing was saved. A required field is empty or a URL is malformed. Check every field with a "
     "red outline and try again."],
    ["A red message says that key, slug or route is already in use",
     "Another item already has that address. Choose a different slug."],
    ["The image I uploaded will not save",
     "It is either larger than the limit or in an unsupported format. Convert it to WebP or JPG and "
     "reduce it to under 3 MB for products, 5 MB for the media library."],
    ["A page shows a broken image",
     "The image was deleted from the media library, or the path was typed incorrectly. Re-upload it "
     "and paste the exact path."],
    ["A menu link goes to the wrong place",
     "Search engine optimisation -> Links and URLs. Find the row and correct its destination."],
    ["Fields the developer mentioned are missing",
     "Press <b>Re-check database and content keys</b> on the dashboard."],
    ["I cannot sign in",
     "The ID is a mobile number, not an email. If it still fails, ask the owner to reset your password "
     "in Admin accounts - it cannot be recovered, only replaced."],
], [5.4 * cm, 11 * cm]))

story.append(Spacer(1, 10))
story.append(callout("When you are not sure, ask",
                     "Nothing in the Content section can permanently damage the website - wording can "
                     "always be typed back. Deleting items and editing the custom code boxes are the two "
                     "genuinely risky actions. If a task involves either and you are not certain, ask "
                     "before you press the button.", "good"))

doc.build(story)
print("written:", OUT)
print("size:", round(os.path.getsize(OUT) / 1024), "KB")
