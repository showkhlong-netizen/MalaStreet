# MALA STREET — IMAGE ASSET MANIFEST & SPECIFICATION SYSTEM
**Document Version:** 1.0.0 (Production Master)  
**Author:** Senior Product Asset Architect & UI Image System Designer  
**Brand:** ร้านหม่าล่าสตรีท (Mala Street)  
**Base URL:** `/assets/`  

---

## ส่วนที่ 1: ภาพรวมหมวดหมู่และมาตรฐานขนาด (Asset Summary Table)

| หมวดหมู่ (Category) | ขนาดมาตรฐาน (Master Res) | สัดส่วน (Aspect Ratio) | รูปแบบไฟล์ที่แนะนำ | การแสดงผล UI (Container Size) | CSS Object-Fit |
|---|---|---|---|---|---|
| **brand (โลโก้สี่เหลี่ยม)** | 512 × 512 px | 1:1 (Square) | WebP / SVG | 64×64 px (Mobile Card), 120×120 px (Desktop) | `cover` / `contain` |
| **brand (โลโก้แนวนอน)** | 1200 × 420 px | 20:7 (2.85:1) | WebP / SVG | 240×84 px (Header / Receipt) | `contain` |
| **hero (แบนเนอร์หลัก)** | 1440 × 700 px | ~2.05:1 (Banner) | WebP (85% Q) | 100% × 270 px (Mobile), 100% × 420 px (Desktop) | `cover` |
| **promotions (แบนเนอร์โปร)** | 1200 × 500 / 360 px | 2.4:1 / 3.33:1 | WebP (82% Q) | 100% × 120–160 px | `cover` |
| **categories (หมวดอาหาร)** | 160 × 160 px | 1:1 (Square) | WebP / SVG | 56×56 px (Mobile Circle Badge) | `cover` (อาหาร) / `contain` (ไอคอน) |
| **menu: featured (เมนูแนะนำ)** | 800 × 520 px | 20:13 (1.54:1) | WebP (85% Q) | 172×112 px (Mobile Horizontal Card) | `cover` |
| **menu: thumbnails (รายการอาหาร)** | 640 × 480 px | 4:3 (Standard) | WebP (85% Q) | 80×80 px / 96×96 px (Mobile List Row) | `cover` |
| **menu: detail (หน้าปรับแต่ง)** | 1200 × 900 px | 4:3 (Standard) | WebP (88% Q) | 100% × 176 px (Modal Header) | `cover` |
| **states (ภาพสถานะระบบ)** | 800 × 800 px | 1:1 (Square) | SVG / WebP | 120×120 px ถึง 160×160 px | `contain` |
| **placeholders (ภาพสำรอง)** | ตามประเภทต้นฉบับ | ตรงกับประเภท | SVG / WebP | ตรงกับคอนเทนเนอร์เป้าหมาย | `contain` / `cover` |

---

## ส่วนที่ 2: รายละเอียด Image Asset Manifest ทั้งหมด (Full Detailed Manifest)

### 2.1 หมวด Brand (`/assets/brand/`)

#### 1. `brand-logo-square`
- **asset_name:** `brand-logo-square`
- **display_name:** โลโก้ร้านแบบสี่เหลี่ยมจัตุรัส (Square Brand Logo)
- **file_name:** `logo-square.webp`
- **public_url:** `/assets/brand/logo-square.webp`
- **category:** `brand`
- **purpose:** แสดงเป็นโลโก้หลักของร้านบนการ์ดหน้าร้าน, PWA App Icon, และ Favicon
- **recommended_format:** WebP (Master) + SVG (Vector Backup)
- **width_px:** 512
- **height_px:** 512
- **aspect_ratio:** 1:1
- **ui_container_width:** 64px (Mobile Card) / 120px (Desktop / Dashboard)
- **ui_container_height:** 64px (Mobile Card) / 120px (Desktop / Dashboard)
- **object_fit:** `cover` (เมื่อใส่กล่องมีขอบมน `rounded-2xl`)
- **safe_area_notes:** ขอบปลอดภัย 64px รอบด้าน (12.5% padding) เพื่อไม่ให้สัญลักษณ์ไม้หม่าล่าและฟอนต์ถูกตัดเมื่อถูก crop เป็นมุมมนหรือวงกลม
- **visual_description:** พื้นหลังกล่องสีแดงสดเข้ม (#D71920) ตรงกลางมีกราฟิกไม้ปิ้งหม่าล่าสีขาวพร้อมเปลวไฟสีส้ม และตัวอักษรภาษาไทย "หม่าล่า" สีขาวเด่นชัด พร้อมซับไตเติล "Mala Street"
- **fallback_asset:** `/assets/placeholders/store-placeholder.svg`
- **alt_text:** โลโก้ร้านหม่าล่าสตรีท Mala Street

#### 2. `brand-logo-horizontal`
- **asset_name:** `brand-logo-horizontal`
- **display_name:** โลโก้ร้านแบบแนวนอน (Horizontal Brand Logo)
- **file_name:** `logo-horizontal.webp`
- **public_url:** `/assets/brand/logo-horizontal.webp`
- **category:** `brand`
- **purpose:** แสดงบน Navigation Bar เดสก์ท็อป, ใบเสร็จรับเงิน (Receipt Header), และเอกสารทางการ
- **recommended_format:** WebP + SVG
- **width_px:** 1200
- **height_px:** 420
- **aspect_ratio:** 20:7 (2.85:1)
- **ui_container_width:** 240px
- **ui_container_height:** 84px
- **object_fit:** `contain`
- **safe_area_notes:** ขอบปลอดภัย 40px บน-ล่าง และ 60px ซ้าย-ขวา
- **visual_description:** โลโก้แนวนอนพื้นโปร่งใส (Transparent) ด้านซ้ายเป็นไอคอนไม้ปิ้งหม่าล่า ด้านขวาเป็นคำว่า "หม่าล่า Mala Street" ตัวหนา สไตล์โมเดิร์น พร้อมสโลแกน "เผ็ดชา ต้นตำรับ"
- **fallback_asset:** `/assets/placeholders/store-placeholder.svg`
- **alt_text:** โลโก้แนวนอนร้านหม่าล่าสตรีท เผ็ดชา ต้นตำรับ

#### 3. `brand-logo-card`
- **asset_name:** `brand-logo-card`
- **display_name:** โลโก้ประกอบการ์ดร้านค้า (Floating Card Logo)
- **file_name:** `logo-card.webp`
- **public_url:** `/assets/brand/logo-card.webp`
- **category:** `brand`
- **purpose:** ไอคอนร้านในกล่องมนสีแดงของการ์ด Floating Store Card ด้านบน
- **recommended_format:** WebP
- **width_px:** 400
- **height_px:** 400
- **aspect_ratio:** 1:1
- **ui_container_width:** 64px
- **ui_container_height:** 64px
- **object_fit:** `cover`
- **safe_area_notes:** วัตถุหลักอยู่กึ่งกลาง เว้นขอบ 15% เผื่อ `rounded-2xl`
- **visual_description:** ไอคอนกราฟิกหม่าล่าสตรีทสีขาว บนพื้นการ์ดไล่เฉดแดงเบอร์กันดี (#D71920 ถึง #A60914) คมชัดระดับ Retina Display
- **fallback_asset:** `/assets/placeholders/store-placeholder.svg`
- **alt_text:** ไอคอนร้านหม่าล่าสตรีท

---

### 2.2 หมวด Hero Banner (`/assets/hero/`)

#### 4. `hero-main-mala`
- **asset_name:** `hero-main-mala`
- **display_name:** ภาพแบนเนอร์หลักชามหม่าล่าเสียบไม้ (Main Hero Spicy Skewers Banner)
- **file_name:** `hero-main-mala.webp`
- **public_url:** `/assets/hero/hero-main-mala.webp`
- **category:** `hero`
- **purpose:** แสดงเป็นภาพฉากหลังส่วนบนสุดของแอป (Hero Header)
- **recommended_format:** WebP (Quality 85%)
- **width_px:** 1440
- **height_px:** 700
- **aspect_ratio:** ~2.05:1
- **ui_container_width:** 100% (Mobile 414–440px / Desktop 100%)
- **ui_container_height:** 270px (Mobile) / 420px (Desktop)
- **object_fit:** `cover`
- **safe_area_notes:** **สำคัญมาก**: จุดรวมสายตาของอาหาร (ชามหม่าล่า, ควัน, พริก และงา) ต้องอยู่กึ่งกลางเยื้องไปทางขวา. พื้นที่ขอบบน 80px (สำหรับปุ่ม Back, Search, Favorite, Share) และขอบล่าง 120px (พื้นที่ที่การ์ดข้อมูลร้านสีขาวจะลอยขึ้นมาทับ) ต้องไม่มีจุดสำคัญของอาหาร
- **visual_description:** ภาพถ่ายกระทะร้อน/ชามหม่าล่าเสียบไม้ย่างเตาถ่านฉ่ำซอสพริกเสฉวนสีแดงเข้ม โรยงาขาวคั่วและต้นหอมซอย มีไอควันความร้อนลอยเบาๆ ถ่ายด้วยเลนส์มาโครชัดลึกสวยงาม ไม่มีข้อความติดในภาพ
- **fallback_asset:** `/assets/placeholders/banner-placeholder.svg`
- **alt_text:** ชามหม่าล่าปิ้งย่างเสียบไม้พ่นควันฉ่ำซอสพริกเสฉวนต้นตำรับ

---

### 2.3 หมวด Promotions (`/assets/promotions/`)

#### 5. `promo-welcome-discount`
- **asset_name:** `promo-welcome-discount`
- **display_name:** แบนเนอร์โปรโมชั่นต้อนรับลูกค้าใหม่ (Welcome Discount Promo)
- **file_name:** `promo-welcome.webp`
- **public_url:** `/assets/promotions/promo-welcome.webp`
- **category:** `promotions`
- **purpose:** แสดงใน Carousel แบนเนอร์โปรโมชั่น หรือหน้า `/promotions`
- **recommended_format:** WebP
- **width_px:** 1200
- **height_px:** 500
- **aspect_ratio:** 2.4:1 (12:5)
- **ui_container_width:** 100% (Max 440px Mobile)
- **ui_container_height:** 160px
- **object_fit:** `cover`
- **safe_area_notes:** ข้อความโปรโมชั่นให้เรนเดอร์ด้วย HTML/CSS Overlay ไม่ฝังในรูปภาพ; องค์ประกอบอาหารอยู่ฝั่งขวา เว้นพื้นที่ด้านซ้าย 50% สำหรับข้อความ
- **visual_description:** เซ็ตหม่าล่าเนื้อและหมูสามชั้นจัดเรียงสวยงามบนถาดไม้ มีพริกหม่าล่าแห้งและโป๊ยกั๊กตกแต่ง แสงนุ่มโทนอบอุ่น
- **fallback_asset:** `/assets/placeholders/banner-placeholder.svg`
- **alt_text:** โปรโมชั่นต้อนรับลูกค้าใหม่ ร้านหม่าล่าสตรีท

#### 6. `promo-combo-party`
- **asset_name:** `promo-combo-party`
- **display_name:** แบนเนอร์ชุดเซ็ตปาร์ตี้ 20 ไม้ (Mala Combo Party Set)
- **file_name:** `promo-combo-party.webp`
- **public_url:** `/assets/promotions/promo-combo-party.webp`
- **category:** `promotions`
- **purpose:** แบนเนอร์ส่งเสริมการขายเซ็ตสุดคุ้ม
- **recommended_format:** WebP
- **width_px:** 1200
- **height_px:** 360
- **aspect_ratio:** 3.33:1 (10:3)
- **ui_container_width:** 100%
- **ui_container_height:** 120px
- **object_fit:** `cover`
- **safe_area_notes:** อาหารอยู่ตรงกลางกึ่งกลางแนวนอน เว้นขอบซ้ายขวาอย่างละ 15%
- **visual_description:** ไม้หม่าล่ารวมมิตร 20 ไม้วางแผ่พัดเป็นรูปพัดบนใบตอง แซมด้วยเห็ดออรินจิและไส้กรอก รมควันหอมกรุ่น
- **fallback_asset:** `/assets/placeholders/banner-placeholder.svg`
- **alt_text:** แบนเนอร์ชุดเซ็ตปาร์ตี้หม่าล่า 20 ไม้สุดคุ้ม

#### 7. `promo-free-delivery`
- **asset_name:** `promo-free-delivery`
- **display_name:** แบนเนอร์โปรโมชั่นส่งฟรี (Free Delivery Campaign)
- **file_name:** `promo-free-delivery.webp`
- **public_url:** `/assets/promotions/promo-free-delivery.webp`
- **category:** `promotions`
- **purpose:** แบนเนอร์แจ้งสิทธิพิเศษส่งฟรีเมื่อสั่งเดลิเวอรี
- **recommended_format:** WebP
- **width_px:** 1200
- **height_px:** 360
- **aspect_ratio:** 3.33:1
- **ui_container_width:** 100%
- **ui_container_height:** 120px
- **object_fit:** `cover`
- **safe_area_notes:** เว้นพื้นที่ว่างฝั่งซ้าย 60% สำหรับข้อความเงื่อนไข
- **visual_description:** ภาพรถมอเตอร์ไซค์ส่งอาหารสไตล์โมเดิร์นสีแดงคู่กับกล่องอาหารหม่าล่าที่ปิดผนึกอย่างดี
- **fallback_asset:** `/assets/placeholders/banner-placeholder.svg`
- **alt_text:** โปรโมชั่นจัดส่งอาหารฟรี ร้านหม่าล่าสตรีท

---

### 2.4 หมวดหมู่อาหาร (`/assets/categories/` - 160x160 px, 1:1, Clean Studio Isolated)

*ข้อกำหนดภาพ Category:* ทุกรูปต้องตัดฉากหลัง (Isolated White/Transparent) หรือเป็นภาพถ่ายบนจานเซรามิกกลมมุม Top-down โทนแสงเดียวกัน เพื่อให้รูปทรงกลมขนาด 56x56 px ดูเป็นเนื้อเดียวกัน

#### 8. `cat-all`
- **asset_name:** `cat-all`
- **display_name:** หมวดหมู่: ทั้งหมด (All Categories)
- **file_name:** `cat-all.svg` (หรือ `cat-all.webp`)
- **public_url:** `/assets/categories/cat-all.svg`
- **category:** `categories`
- **purpose:** ปุ่มหมวดหมู่แรกสำหรับแสดงสินค้าทุกรายการ
- **recommended_format:** SVG (Vector Monochrome Red)
- **width_px:** 160
- **height_px:** 160
- **aspect_ratio:** 1:1
- **ui_container_width:** 56px (Circle/Rounded-2xl)
- **ui_container_height:** 56px
- **object_fit:** `contain` (พร้อม padding 12px)
- **safe_area_notes:** ไอคอน 4 สี่เหลี่ยมมนสีแดงสด (#D71920) อยู่กึ่งกลาง
- **visual_description:** สัญลักษณ์ 4 จุดสี่เหลี่ยมขอบมนสีแดง สื่อถึงเมนูทั้งหมด เรียบหรู สะอาดตา
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** เมนูทั้งหมด หม่าล่าสตรีท

#### 9. `cat-beef`
- **asset_name:** `cat-beef`
- **display_name:** หมวดหมู่: เนื้อโคขุน (Premium Beef)
- **file_name:** `cat-beef.webp`
- **public_url:** `/assets/categories/cat-beef.webp`
- **category:** `categories`
- **purpose:** ไอคอนปุ่มหมวดเนื้อวัวโคขุน
- **recommended_format:** WebP (Transparent/Clean Background)
- **width_px:** 160
- **height_px:** 160
- **aspect_ratio:** 1:1
- **ui_container_width:** 56px (Circle)
- **ui_container_height:** 56px
- **object_fit:** `cover`
- **safe_area_notes:** ชิ้นเนื้อลายหินอ่อนสดอยู่ตรงกลางรัศมีวงกลม ไม่ชิดขอบ
- **visual_description:** เนื้อโคขุนหั่นเต๋าสีแดงสดลายมันแทรกเสียบไม้ ถ่ายมุมตรง แสงสตูดิโอ ไร้ฉากหลังรก
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** หมวดหมู่เนื้อโคขุน

#### 10. `cat-pork-chicken`
- **asset_name:** `cat-pork-chicken`
- **display_name:** หมวดหมู่: หมู & ไก่ (Pork & Chicken)
- **file_name:** `cat-pork-chicken.webp`
- **public_url:** `/assets/categories/cat-pork-chicken.webp`
- **category:** `categories`
- **purpose:** ไอคอนปุ่มหมวดเนื้อหมูและเนื้อไก่
- **recommended_format:** WebP
- **width_px:** 160
- **height_px:** 160
- **aspect_ratio:** 1:1
- **ui_container_width:** 56px
- **ui_container_height:** 56px
- **object_fit:** `cover`
- **safe_area_notes:** ไม้สามชั้นและไก่อยู่กึ่งกลาง ไม่ล้นวงกลม
- **visual_description:** หมูสามชั้นสไลซ์ม้วนชั้นสวยงามคู่กับเนื้อไก่หมักเสียบไม้ สีชมพูสะอาด สดใหม่
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** หมวดหมู่หมูและไก่

#### 11. `cat-meatballs`
- **asset_name:** `cat-meatballs`
- **display_name:** หมวดหมู่: ลูกชิ้น & ไส้กรอก (Meatballs & Sausages)
- **file_name:** `cat-meatballs.webp`
- **public_url:** `/assets/categories/cat-meatballs.webp`
- **category:** `categories`
- **purpose:** ไอคอนปุ่มหมวดลูกชิ้นและไส้กรอก
- **recommended_format:** WebP
- **width_px:** 160
- **height_px:** 160
- **aspect_ratio:** 1:1
- **ui_container_width:** 56px
- **ui_container_height:** 56px
- **object_fit:** `cover`
- **safe_area_notes:** ลูกชิ้นกลมมน 3 ลูกจัดเรียงแนวทแยงตรงกลาง
- **visual_description:** ลูกชิ้นปลาขาวเด้งและลูกชิ้นเนื้อเนียนเสียบไม้เรียงกัน สดสะอาด
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** หมวดหมู่ลูกชิ้น

#### 12. `cat-vegetables`
- **asset_name:** `cat-vegetables`
- **display_name:** หมวดหมู่: ผักสด & เห็ด (Fresh Vegetables)
- **file_name:** `cat-vegetables.webp`
- **public_url:** `/assets/categories/cat-vegetables.webp`
- **category:** `categories`
- **purpose:** ไอคอนปุ่มหมวดผักสดและเห็ด
- **recommended_format:** WebP
- **width_px:** 160
- **height_px:** 160
- **aspect_ratio:** 1:1
- **ui_container_width:** 56px
- **ui_container_height:** 56px
- **object_fit:** `cover`
- **safe_area_notes:** ผักกาดขาวและเห็ดออรินจิเขียวสดใส อยู่ในเซ็นเตอร์
- **visual_description:** ผักกาดขาวสดกรอบประกบคู่กับเห็ดเข็มทองสีขาวสะอาด สดฉ่ำน้ำ
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** หมวดหมู่ผัก

#### 13. `cat-tofu`
- **asset_name:** `cat-tofu`
- **display_name:** หมวดหมู่: เต้าหู้ & เส้น (Tofu & Noodles)
- **file_name:** `cat-tofu.webp`
- **public_url:** `/assets/categories/cat-tofu.webp`
- **category:** `categories`
- **purpose:** ไอคอนปุ่มหมวดเต้าหู้และเส้นต่างๆ
- **recommended_format:** WebP
- **width_px:** 160
- **height_px:** 160
- **aspect_ratio:** 1:1
- **ui_container_width:** 56px
- **ui_container_height:** 56px
- **object_fit:** `cover`
- **safe_area_notes:** ก้อนเต้าหู้สีเหลืองทองอยู่กึ่งกลาง
- **visual_description:** เต้าหู้ปลาเต๋าเหลืองทองและฟองเต้าหู้ม้วนทอดกรอบน่าทาน
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** หมวดหมู่เต้าหู้และเส้น

#### 14. `cat-snacks`
- **asset_name:** `cat-snacks`
- **display_name:** หมวดหมู่: ของทานเล่น (Crispy Snacks)
- **file_name:** `cat-snacks.webp`
- **public_url:** `/assets/categories/cat-snacks.webp`
- **category:** `categories`
- **purpose:** ไอคอนปุ่มหมวดของทอดและของทานเล่น
- **recommended_format:** WebP
- **width_px:** 160
- **height_px:** 160
- **aspect_ratio:** 1:1
- **ui_container_width:** 56px
- **ui_container_height:** 56px
- **object_fit:** `cover`
- **safe_area_notes:** กล่องของทอดสีทองอร่ามตรงกลาง
- **visual_description:** เฟรนช์ฟรายส์ทอดแท่งทองกรอบคลุกผงหม่าล่าสีส้มแดงเบาๆ
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** หมวดหมู่ของทานเล่น

#### 15. `cat-drinks`
- **asset_name:** `cat-drinks`
- **display_name:** หมวดหมู่: เครื่องดื่ม (Cold Beverages)
- **file_name:** `cat-drinks.webp`
- **public_url:** `/assets/categories/cat-drinks.webp`
- **category:** `categories`
- **purpose:** ไอคอนปุ่มหมวดเครื่องดื่มแก้เผ็ด
- **recommended_format:** WebP
- **width_px:** 160
- **height_px:** 160
- **aspect_ratio:** 1:1
- **ui_container_width:** 56px
- **ui_container_height:** 56px
- **object_fit:** `cover`
- **safe_area_notes:** แก้วน้ำ/กระป๋องเครื่องดื่มตรงกลาง มีหยดน้ำเกาะแสดงความเย็นสดชื่น
- **visual_description:** แก้วน้ำแดงโซดาเย็นฉ่ำมีน้ำแข็งใสและหลอด หรือกระป๋องชาจีนสีแดง
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** หมวดหมู่เครื่องดื่ม

---

### 2.5 หมวด Featured Menu Images (`/assets/menu/featured/` - 800x520 px, ~1.54:1)

*ข้อกำหนดภาพ:* แสดงบนการ์ดเลื่อนแนวนอนของหัวข้อ `เมนูแนะนำ 🔥` ด้านบนของการ์ดจะมี Badge `ขายดี` หรือ `แนะนำ` ลอยอยู่มุมซ้ายบน ดังนั้น **ห้ามวางเนื้อหาสำคัญชิดมุมซ้ายบน**

#### 16. `feat-beef-premium`
- **asset_name:** `feat-beef-premium`
- **display_name:** เมนูแนะนำ: เนื้อโคขุนพรีเมียม (Grilled Premium Beef)
- **file_name:** `feat-beef-premium.webp`
- **public_url:** `/assets/menu/featured/feat-beef-premium.webp`
- **category:** `menu`
- **purpose:** การ์ดเมนูแนะนำอันดับ 1 (Badge: 👑 ขายดี)
- **recommended_format:** WebP (85% Quality)
- **width_px:** 800
- **height_px:** 520
- **aspect_ratio:** 20:13 (1.54:1)
- **ui_container_width:** 172px
- **ui_container_height:** 112px
- **object_fit:** `cover`
- **safe_area_notes:** เว้นพื้นที่มุมซ้ายบน 60x30px สำหรับป้าย Badge `ขายดี`; ตัวเนื้อย่างเสียบไม้พาดแนวทแยงมุมจากขวาลงมากลาง
- **visual_description:** เนื้อโคขุนเสียบไม้ย่างเตาถ่านเกรียมกำลังดี ชุ่มฉ่ำด้วยซอสหม่าล่าสีแดงเข้ม โรยงาขาวคั่วและพริกหม่าล่าบดหยาบ แสงไฟอุ่นสะท้อนความมันวาว
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** เนื้อโคขุนพรีเมียมย่างหม่าล่าเตาถ่าน

#### 17. `feat-pork-sliced`
- **asset_name:** `feat-pork-sliced`
- **display_name:** เมนูแนะนำ: หมูสามชั้นสไลซ์ (Grilled Sliced Pork Belly)
- **file_name:** `feat-pork-sliced.webp`
- **public_url:** `/assets/menu/featured/feat-pork-sliced.webp`
- **category:** `menu`
- **purpose:** การ์ดเมนูแนะนำอันดับ 2 (Badge: 👍 แนะนำ)
- **recommended_format:** WebP
- **width_px:** 800
- **height_px:** 520
- **aspect_ratio:** 1.54:1
- **ui_container_width:** 172px
- **ui_container_height:** 112px
- **object_fit:** `cover`
- **safe_area_notes:** เว้นมุมซ้ายบนสำหรับ Badge `แนะนำ`
- **visual_description:** หมูสามชั้นสไลซ์เสียบไม้รีดน้ำมันย่างกรอบนอกนุ่มใน ทาซอสหม่าล่าประกายทองแดง หอมกลิ่นเตาถ่าน
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** หมูสามชั้นสไลซ์ย่างหม่าล่า

#### 18. `feat-meatball-mix`
- **asset_name:** `feat-meatball-mix`
- **display_name:** เมนูแนะนำ: ลูกชิ้นรวม (Grilled Assorted Meatballs)
- **file_name:** `feat-meatball-mix.webp`
- **public_url:** `/assets/menu/featured/feat-meatball-mix.webp`
- **category:** `menu`
- **purpose:** การ์ดเมนูแนะนำอันดับ 3 (Badge: 🔥 ยอดนิยม)
- **recommended_format:** WebP
- **width_px:** 800
- **height_px:** 520
- **aspect_ratio:** 1.54:1
- **ui_container_width:** 172px
- **ui_container_height:** 112px
- **object_fit:** `cover`
- **safe_area_notes:** ลูกชิ้นเรียงแถวแน่นตรงกลาง เว้นมุมซ้ายบนสำหรับ Badge
- **visual_description:** ลูกชิ้นปลา ลูกชิ้นกุ้ง และลูกชิ้นเอ็นเสียบไม้ย่างผิวนอกพองกรอบ เคลือบน้ำจิ้มหม่าล่าข้นหวานเผ็ด
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** ลูกชิ้นรวมปิ้งย่างซอสหม่าล่า

---

### 2.6 หมวด Menu Thumbnails & Catalog (`/assets/menu/thumbnails/` - 640x480 px, 4:3)

*ข้อกำหนดภาพ:* แสดงเป็นภาพ Thumbnail ทางซ้ายของการ์ดแถวรายการอาหารในหน้าหลัก (Container ขนาด 80x80 px ถึง 96x96 px ขอบมน `rounded-2xl`)

| Asset Name | Display Name | File Name | Public URL | Width x Height | Description | Alt Text |
|---|---|---|---|---|---|---|
| `thumb-beef-premium` | เนื้อโคขุนพรีเมียม | `beef-premium.webp` | `/assets/menu/thumbnails/beef-premium.webp` | 640×480 | เนื้อโคขุนคัดพิเศษเสียบไม้ สดลายสวยบนจานสีดำ | เนื้อโคขุนพรีเมียมเสียบไม้ |
| `thumb-beef-blade` | เนื้อใบพายโคขุน | `beef-blade.webp` | `/assets/menu/thumbnails/beef-blade.webp` | 640×480 | เนื้อใบพายแทรกเอ็นกรุบกรอบสไลซ์บางเสียบไม้ | เนื้อใบพายโคขุนคัดพิเศษ |
| `thumb-pork-sliced` | หมูสามชั้นสไลซ์ | `pork-sliced.webp` | `/assets/menu/thumbnails/pork-sliced.webp` | 640×480 | สามชั้นสไลซ์บาง มันแทรกพอดี เสียบไม้สวยงาม | หมูสามชั้นสไลซ์เสียบไม้ |
| `thumb-pork-enoki` | หมูสามชั้นพันเห็ดเข็มทอง | `pork-enoki-roll.webp` | `/assets/menu/thumbnails/pork-enoki-roll.webp` | 640×480 | หมูสามชั้นพันรอบเห็ดเข็มทองสด ม้วนแน่นเสียบไม้ | หมูสามชั้นพันเห็ดเข็มทอง |
| `thumb-chicken-mala` | ไก่ย่างหม่าล่า | `chicken-mala.webp` | `/assets/menu/thumbnails/chicken-mala.webp` | 640×480 | สะโพกไก่เนื้อนุ่มหมักซอสหม่าล่าเข้าเนื้อ | ไก่หมักหม่าล่าเสียบไม้ |
| `thumb-chicken-cartilage` | เอ็นไก่กรุบกรอบ | `chicken-cartilage.webp` | `/assets/menu/thumbnails/chicken-cartilage.webp` | 640×480 | เอ็นข้อไก่คัดพิเศษ เคี้ยวกรุบกรอบเพลิน | เอ็นข้อไก่กรุบกรอบ |
| `thumb-meatball-mix` | ลูกชิ้นรวมมิตร | `meatball-mix.webp` | `/assets/menu/thumbnails/meatball-mix.webp` | 640×480 | ลูกชิ้นปลา หมู และเนื้อ คละชนิดเสียบไม้ | ลูกชิ้นรวมเสียบไม้ |
| `thumb-fish-ball` | ลูกชิ้นปลาภูเก็ตแท้ | `fish-ball.webp` | `/assets/menu/thumbnails/fish-ball.webp` | 640×480 | ลูกชิ้นปลาแท้เนื้อเด้ง ไม่คาว ขาวเนียน | ลูกชิ้นปลาแท้ระเบิด |
| `thumb-fish-tofu` | เต้าหู้ปลาแผ่น | `fish-tofu.webp` | `/assets/menu/thumbnails/fish-tofu.webp` | 640×480 | เต้าหู้ปลาทรงสี่เหลี่ยมเนื้อนุ่มเนียน | เต้าหู้ปลาเสียบไม้ |
| `thumb-cheese-tofu` | เต้าหู้ชีสลาวา | `cheese-tofu.webp` | `/assets/menu/thumbnails/cheese-tofu.webp` | 640×480 | เต้าหู้ชีสสอดไส้ชีสเยิ้ม กัดทะลัก | เต้าหู้ชีสลาวาเสียบไม้ |
| `thumb-napa-cabbage` | ผักกาดขาวหวานกรอบ | `napa-cabbage.webp` | `/assets/menu/thumbnails/napa-cabbage.webp` | 640×480 | ผักกาดขาวสดล้างสะอาด พับซ้อนเสียบไม้ | ผักกาดขาวเสียบไม้ |
| `thumb-enoki` | เห็ดเข็มทองสด | `enoki.webp` | `/assets/menu/thumbnails/enoki.webp` | 640×480 | ช่อเห็ดเข็มทองขาวสะอาด คัดเกรดสดใหม่ | เห็ดเข็มทองเสียบไม้ |
| `thumb-baby-corn` | ข้าวโพดอ่อนหวานกรอบ | `baby-corn.webp` | `/assets/menu/thumbnails/baby-corn.webp` | 640×480 | ข้าวโพดอ่อนสีเหลืองนวล ฝักยาวพอดีคำ | ข้าวโพดอ่อนเสียบไม้ |
| `thumb-instant-noodle` | บะหมี่กึ่งสำเร็จรูปจีน | `instant-noodle.webp` | `/assets/menu/thumbnails/instant-noodle.webp` | 640×480 | ก้อนบะหมี่เส้นเหนียวนุ่ม สำหรับต้มซุปหม่าล่า | บะหมี่จีนสำหรับซุปหม่าล่า |
| `thumb-glass-noodle` | เส้นมันหวานจีนหนึบ | `glass-noodle.webp` | `/assets/menu/thumbnails/glass-noodle.webp` | 640×480 | เส้นมันเทศใสแบน เหนียวหนึบ ดูดซับน้ำซุปดีเลิศ | เส้นมันหวานจีนหนึบหนับ |
| `thumb-french-fries` | เฟรนช์ฟรายส์คลุกหม่าล่า | `french-fries.webp` | `/assets/menu/thumbnails/french-fries.webp` | 640×480 | มันฝรั่งทอดแท่งทองกรอบ โรยผงหม่าล่าเข้มข้น | เฟรนช์ฟรายส์หม่าล่า |
| `thumb-soft-drink` | น้ำอัดลมกระป๋อง | `soft-drink.webp` | `/assets/menu/thumbnails/soft-drink.webp` | 640×480 | โค้ก/สไปรท์กระป๋องเย็นเจี๊ยบ มีหยดน้ำเกาะ | เครื่องดื่มน้ำอัดลมเย็น |
| `thumb-thai-tea-lemon` | ชาสมุนไพรจีน / หวังเหล่าจี๋ | `thai-tea-lemon.webp` | `/assets/menu/thumbnails/thai-tea-lemon.webp` | 640×480 | กระป๋องแดงชาสมุนไพรจีนแก้เผ็ด หรือชาดำมะนาว | หวังเหล่าจี๋ ชาสมุนไพรจีนแก้เผ็ด |

---

### 2.7 หมวด State Illustrations (`/assets/states/` - 800x800 px, Vector SVG / Flat Clean Art)

*ข้อกำหนดภาพ:* สไตล์มินิมอล โมเดิร์น ลายเส้นสะอาด คุมธีมสี **แดง แบรนด์ (#D71920), ครีมอ่อน (#FFF1F1), เทาเข้ม (#344054)** ไม่มีลายน้ำ ไม่มีข้อความตายตัวในภาพ

#### 37. `state-empty-cart`
- **asset_name:** `state-empty-cart`
- **display_name:** ภาพสถานะ: ตะกร้าว่าง (Empty Shopping Cart)
- **file_name:** `empty-cart.svg`
- **public_url:** `/assets/states/empty-cart.svg`
- **category:** `states`
- **purpose:** แสดงในลิ้นชักตะกร้า หรือหน้า `/cart` เมื่อยังไม่ได้เพิ่มอาหาร
- **recommended_format:** SVG
- **width_px:** 800
- **height_px:** 800
- **aspect_ratio:** 1:1
- **ui_container_width:** 140px
- **ui_container_height:** 140px
- **object_fit:** `contain`
- **safe_area_notes:** อาร์ตเวิร์กอยู่ตรงกลาง รัศมีเว้นขอบ 10%
- **visual_description:** รถเข็นช้อปปิ้งสไตล์มินิมอลเส้นสีเทา มีไม้หม่าล่าและหัวใจสีแดงลอยเบาๆ สื่อถึงการชวนเลือกอาหาร
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** ตะกร้าอาหารยังว่างอยู่ เลือกเมนูหม่าล่าแสนอร่อยได้เลย

#### 38. `state-empty-search`
- **asset_name:** `state-empty-search`
- **display_name:** ภาพสถานะ: ค้นหาไม่พบเมนู (No Search Results Found)
- **file_name:** `empty-search.svg`
- **public_url:** `/assets/states/empty-search.svg`
- **category:** `states`
- **purpose:** แสดงใน Modal ค้นหา หรือหน้าผลการค้นหาเมื่อผู้ใช้พิมพ์คำที่ไม่ตรงกับเมนูใด
- **recommended_format:** SVG
- **width_px:** 800
- **height_px:** 800
- **aspect_ratio:** 1:1
- **ui_container_width:** 120px
- **ui_container_height:** 120px
- **object_fit:** `contain`
- **safe_area_notes:** กลางภาพ
- **visual_description:** แว่นขยายที่มีประกายดาวสีส้มและชามหม่าล่าว่างเปล่า เส้นสายนุ่มนวล ไม่รกสายตา
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** ไม่พบเมนูอาหารที่คุณค้นหา ลองค้นหาด้วยคำอื่น

#### 39. `state-empty-category`
- **asset_name:** `state-empty-category`
- **display_name:** ภาพสถานะ: หมวดหมู่นี้ไม่มีสินค้า (Empty Category List)
- **file_name:** `empty-category.svg`
- **public_url:** `/assets/states/empty-category.svg`
- **category:** `states`
- **purpose:** แสดงเมื่อคลิกเลือกหมวดหมู่ที่ยังไม่มีการเพิ่มรายการอาหารในขณะนั้น
- **recommended_format:** SVG
- **width_px:** 800
- **height_px:** 800
- **aspect_ratio:** 1:1
- **ui_container_width:** 120px
- **ui_container_height:** 120px
- **object_fit:** `contain`
- **safe_area_notes:** กลางภาพ
- **visual_description:** ถาดเสิร์ฟอาหารสีเทาอ่อนว่างเปล่า มีประกายแสงสีแดงเบาๆ
- **fallback_asset:** `/assets/placeholders/menu-placeholder.svg`
- **alt_text:** ยังไม่มีรายการอาหารในหมวดหมู่นี้

#### 40. `state-order-success`
- **asset_name:** `state-order-success`
- **display_name:** ภาพสถานะ: สั่งซื้อสำเร็จ (Order Placed Successfully)
- **file_name:** `order-success.svg`
- **public_url:** `/assets/states/order-success.svg`
- **category:** `states`
- **purpose:** แสดงในหน้ายืนยันคำสั่งซื้อ `/order/success/:id` และหน้าจอติดตามครัว
- **recommended_format:** SVG
- **width_px:** 800
- **height_px:** 800
- **aspect_ratio:** 1:1
- **ui_container_width:** 160px
- **ui_container_height:** 160px
- **object_fit:** `contain`
- **safe_area_notes:** เครื่องหมายถูกสีเขียวมรกตและเตาถ่านไฟลุกอบอุ่น
- **visual_description:** วงกลมเครื่องหมายถูกสีเขียว (#087F5B) ผสานเข้ากับภาพกล่องอาหารและไม้หม่าล่า มีดาวระยิบระยับ สื่อถึงคำสั่งซื้อส่งเข้าครัวเรียบร้อย
- **fallback_asset:** `/assets/placeholders/store-placeholder.svg`
- **alt_text:** ส่งคำสั่งซื้อสำเร็จ ร้านได้รับออเดอร์แล้ว

#### 41. `state-payment-success`
- **asset_name:** `state-payment-success`
- **display_name:** ภาพสถานะ: ชำระเงินสำเร็จ (Payment Verified Success)
- **file_name:** `payment-success.svg`
- **public_url:** `/assets/states/payment-success.svg`
- **category:** `states`
- **purpose:** แสดงเมื่อตรวจสอบสลิปพร้อมเพย์หรือยืนยันการจ่ายเงินเรียบร้อย
- **recommended_format:** SVG
- **width_px:** 800
- **height_px:** 800
- **aspect_ratio:** 1:1
- **ui_container_width:** 140px
- **ui_container_height:** 140px
- **object_fit:** `contain`
- **safe_area_notes:** กลางภาพ
- **visual_description:** ไอคอนกระเป๋าเงิน/การ์ดพร้อมตราสัญลักษณ์ตรวจสอบความถูกต้องสีเขียวและเหรียญทอง
- **fallback_asset:** `/assets/placeholders/store-placeholder.svg`
- **alt_text:** การชำระเงินได้รับการตรวจสอบและยืนยันสำเร็จ

#### 42. `state-error`
- **asset_name:** `state-error`
- **display_name:** ภาพสถานะ: เกิดข้อผิดพลาด (System Error / Offline)
- **file_name:** `error-state.svg`
- **public_url:** `/assets/states/error-state.svg`
- **category:** `states`
- **purpose:** แสดงเมื่อขาดการเชื่อมต่ออินเทอร์เน็ต โหลด API ไม่ผ่าน หรือ 404/500
- **recommended_format:** SVG
- **width_px:** 800
- **height_px:** 800
- **aspect_ratio:** 1:1
- **ui_container_width:** 140px
- **ui_container_height:** 140px
- **object_fit:** `contain`
- **safe_area_notes:** กลางภาพ
- **visual_description:** ภาพเตาถ่านที่ไฟดับพร้อมปลั๊กไฟหลุด หรือเครื่องหมายตกใจสีแดงส้ม สื่อถึงให้กดลองใหม่อีกครั้ง (Retry)
- **fallback_asset:** `/assets/placeholders/store-placeholder.svg`
- **alt_text:** เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณากดลองใหม่อีกครั้ง

---

### 2.8 หมวด Placeholders (`/assets/placeholders/`)

#### 43. `placeholder-menu`
- **asset_name:** `placeholder-menu`
- **display_name:** ภาพสำรองสินค้าอาหาร (Menu Item Fallback Placeholder)
- **file_name:** `menu-placeholder.svg`
- **public_url:** `/assets/placeholders/menu-placeholder.svg`
- **category:** `placeholders`
- **purpose:** ใช้เป็น Fallback ทันทีเมื่อภาพอาหารในระบบเกิด 404 หรือโหลดไม่สำเร็จ
- **recommended_format:** SVG
- **width_px:** 640
- **height_px:** 480
- **aspect_ratio:** 4:3
- **ui_container_width:** 100% (ตรงตามช่องที่แสดงผล)
- **ui_container_height:** 100%
- **object_fit:** `contain`
- **safe_area_notes:** มีแพดดิ้งรอบใน 20%
- **visual_description:** พื้นหลังกล่องสีครีมชมพูอ่อน (#FFF1F1) ตรงกลางเป็นเส้นกราฟิกมินิมอลไม้หม่าล่า 2 ไม้ไขว้กันสีแดงหม่น (#E28C91) ดูสุภาพ เรียบร้อย ไม่ทำให้หน้าจอแหว่ง
- **fallback_asset:** Inline SVG
- **alt_text:** รูปภาพเมนูอาหารหม่าล่าสตรีท

#### 44. `placeholder-store`
- **asset_name:** `placeholder-store`
- **display_name:** ภาพสำรองข้อมูลร้านค้า (Store Profile Fallback Placeholder)
- **file_name:** `store-placeholder.svg`
- **public_url:** `/assets/placeholders/store-placeholder.svg`
- **category:** `placeholders`
- **purpose:** สำรองโลโก้หรือหน้าร้าน
- **recommended_format:** SVG
- **width_px:** 512
- **height_px:** 512
- **aspect_ratio:** 1:1
- **ui_container_width:** 100%
- **ui_container_height:** 100%
- **object_fit:** `contain`
- **safe_area_notes:** กลางภาพ
- **visual_description:** ภาพหน้าร้านค้ามินิมอลสีแดงสตรีท พร้อมไอคอนช้อนส้อม
- **fallback_asset:** Inline SVG
- **alt_text:** ข้อมูลร้านหม่าล่าสตรีท

#### 45. `placeholder-banner`
- **asset_name:** `placeholder-banner`
- **display_name:** ภาพสำรองแบนเนอร์ (Promo/Hero Banner Fallback Placeholder)
- **file_name:** `banner-placeholder.svg`
- **public_url:** `/assets/placeholders/banner-placeholder.svg`
- **category:** `placeholders`
- **purpose:** สำรองเวลา Hero Banner หรือ Promo Banner โหลดไม่ผ่าน
- **recommended_format:** SVG
- **width_px:** 1200
- **height_px:** 500
- **aspect_ratio:** 2.4:1
- **ui_container_width:** 100%
- **ui_container_height:** 100%
- **object_fit:** `cover`
- **safe_area_notes:** ไล่เฉดสีแบรนด์
- **visual_description:** พื้นหลัง Gradient สวยงามไล่เฉดสีแดงเข้ม #7E000B ไปยัง #C8101E พร้อมลวดลายเรขาคณิตจางๆ สไตล์จีนร่วมสมัย
- **fallback_asset:** CSS Gradient `#7E000B`
- **alt_text:** แบนเนอร์ร้านหม่าล่าสตรีท

---

## ส่วนที่ 3: Frontend Integration Guide สำหรับทีมพัฒนา

### 3.1 สรุปกฎการใช้งาน CSS `object-fit` และ ขนาดคอนเทนเนอร์

| ประเภทรูปภาพ | คอนเทนเนอร์ UI | ค่า `object-fit` | เหตุผลในการเลือก |
|---|---|---|---|
| **Hero Banner** | Width 100%, Height 270px (Mobile) / 420px (Desktop) | `object-fit: cover; object-position: center;` | เพื่อให้ภาพอาหารเติมเต็มพื้นที่แบนเนอร์โดยไม่มีขอบขาวว่าง |
| **Category Badges** | Width 56px, Height 56px (วงกลม `rounded-full`) | `object-fit: cover;` (รูปถ่าย) / `object-fit: contain;` (ไอคอน) | รูปถ่ายอาหารต้องเต็มวงกลมพอดี; ไอคอนเวกเตอร์ต้องมีช่องไฟไม่ชิดขอบ |
| **Featured Menu Cards** | Width 172px, Height 112px (`rounded-t-2xl`) | `object-fit: cover;` | รักษาสัดส่วนภาพย่างเกรียม ไม่บิดเบี้ยว ย่อขยายเต็มการ์ด |
| **Menu List Thumbnails** | Width 80px, Height 80px (`rounded-2xl`) | `object-fit: cover;` | แสดงวัตถุดิบและอาหารชัดเจนในกรอบสี่เหลี่ยมจัตุรัส |
| **Product Detail Modal** | Width 100%, Height 176px (Modal Header) | `object-fit: cover;` | ให้ความรู้สึกพรีเมียม เต็มตาใน Bottom Sheet |
| **Brand Logos** | 64×64px (Card) / 240×84px (Header) | `object-fit: contain;` | ตัวหนังสือและสัญลักษณ์โลโก้ต้องสมบูรณ์ 100% ไม่ถูกตัดขอบ |
| **State Illustrations** | 120×120px ถึง 160×160px | `object-fit: contain;` | กราฟิกเวกเตอร์ตรงกลาง มีที่ว่างรอบข้างเพื่อความสบายตา |

---

### 3.2 ระบบ Fallback อัตโนมัติเมื่อรูปภาพไม่พบ (Fail-safe Fallback Strategy)

ในฝั่ง Frontend (Blade / React / Vue) ให้ใช้ Helper Function หรือ Event `onerror` เพื่อป้องกันภาพแตกหรือเกิดกรอบว่าง:

#### การใช้งานใน HTML / Blade View:
```html
<!-- ตัวอย่างการแสดงผลรูปเมนูอาหารพร้อม Fallback อัตโนมัติ -->
<img src="{{ $product->image_url ?? asset('assets/menu/thumbnails/beef-premium.webp') }}" 
     alt="{{ $product->name }}" 
     class="w-20 h-20 rounded-2xl object-cover bg-slate-100"
     loading="lazy"
     decoding="async"
     onerror="this.onerror=null; this.src='/assets/placeholders/menu-placeholder.svg';">
```

#### แผนผังลำดับ Fallback (Fallback Waterfall):
```
1. Specific Menu Image (เช่น /assets/menu/thumbnails/pork-sliced.webp)
       ↓ (หาก HTTP 404 / โหลดไม่ผ่าน)
2. Category Generic Image (เช่น /assets/categories/cat-pork-chicken.webp)
       ↓ (หากไม่มี)
3. Universal Menu Placeholder (/assets/placeholders/menu-placeholder.svg)
       ↓ (หาก Asset หลุด)
4. Inline Embedded SVG Data URI (ไม่พึ่งพา Network)
```

---

### 3.3 พฤติกรรม Responsive บน Mobile & หน้าจอ Retina (High DPI)

1. **จอ Retina Display (2x / 3x):**
   - ไฟล์ต้นฉบับ Master มีขนาด Pixel เป็น 2 เท่า (2x) ของ UI Container เสมอ (เช่น คอนเทนเนอร์แสดงผล 80×80 px แต่ Master Asset มีขนาด 640×480 px หรือ 160×160 px) จึงทำให้ภาพคมกริบ ไร้รอยเบลอบนหน้าจอ iPhone Super Retina และ Android AMOLED
2. **การป้องกัน Layout Shift (CLS = 0):**
   - คอนเทนเนอร์ทุกตัวต้องระบุ `aspect-ratio` หรือกำหนด `w-[80px] h-[80px]` ใน CSS เสมอ เพื่อจองพื้นที่ไว้ล่วงหน้าก่อนรูปดาวน์โหลดเสร็จ
3. **การประหยัดแบนด์วิดท์:**
   - ใช้ `loading="lazy"` สำหรับภาพในรายการอาหารทั้งหมด ยกเว้น **Hero Banner** ด้านบนสุดที่ต้องโหลดทันทีโดยใช้ `fetchpriority="high"`

---

### 3.4 โครงสร้างไฟล์ในโฟลเดอร์โปรเจกต์จริง (`public/assets/`)

```text
public/
└── assets/
    ├── brand/
    │   ├── logo-square.webp
    │   ├── logo-horizontal.webp
    │   └── logo-card.webp
    ├── hero/
    │   └── hero-main-mala.webp
    ├── promotions/
    │   ├── promo-welcome.webp
    │   ├── promo-combo-party.webp
    │   └── promo-free-delivery.webp
    ├── categories/
    │   ├── cat-all.svg
    │   ├── cat-beef.webp
    │   ├── cat-pork-chicken.webp
    │   ├── cat-meatballs.webp
    │   ├── cat-vegetables.webp
    │   ├── cat-tofu.webp
    │   ├── cat-snacks.webp
    │   └── cat-drinks.webp
    ├── menu/
    │   ├── featured/
    │   │   ├── feat-beef-premium.webp
    │   │   ├── feat-pork-sliced.webp
    │   │   └── feat-meatball-mix.webp
    │   └── thumbnails/
    │       ├── beef-premium.webp
    │       ├── beef-blade.webp
    │       ├── pork-sliced.webp
    │       ├── pork-enoki-roll.webp
    │       ├── chicken-mala.webp
    │       ├── chicken-cartilage.webp
    │       ├── meatball-mix.webp
    │       ├── fish-ball.webp
    │       ├── fish-tofu.webp
    │       ├── cheese-tofu.webp
    │       ├── napa-cabbage.webp
    │       ├── enoki.webp
    │       ├── baby-corn.webp
    │       ├── instant-noodle.webp
    │       ├── glass-noodle.webp
    │       ├── french-fries.webp
    │       ├── soft-drink.webp
    │       └── thai-tea-lemon.webp
    ├── states/
    │   ├── empty-cart.svg
    │   ├── empty-search.svg
    │   ├── empty-category.svg
    │   ├── order-success.svg
    │   ├── payment-success.svg
    │   └── error-state.svg
    └── placeholders/
        ├── menu-placeholder.svg
        ├── store-placeholder.svg
        └── banner-placeholder.svg
```

---

### 3.5 การเปลี่ยนเป็นภาพจริงของร้านในภายหลัง (Future Asset Swapping)
- เจ้าของร้านสามารถบันทึกภาพถ่ายสินค้าจริงของร้านในขนาดและชื่อไฟล์เดียวกับใน Manifest แล้วนำไปวางทับไฟล์เดิมในโฟลเดอร์ `public/assets/...` ได้ทันที โดยระบบ UI ทั้งหมดจะอัปเดตอัตโนมัติ 100% โดยไม่ต้องแก้โค้ดโปรแกรมใดๆ เพิ่มเติม
- ระบบหลังบ้าน (Admin Catalog) รองรับการอัปโหลดและอัปเดตฟิลด์ `products.image_url` ในฐานข้อมูลได้โดยตรง

