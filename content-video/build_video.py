"""Build the '73 CV' TikTok video: HTML frames -> PNG (Chrome), edge-tts voice, ffmpeg assembly."""
import asyncio, json, os, subprocess, sys
from pathlib import Path
import edge_tts

ROOT = Path(__file__).parent
WORK = ROOT / "work"
WORK.mkdir(exist_ok=True)
PHOTO = (ROOT / "nhan-vat.webp").resolve()
OUT = Path(sys.argv[1]) if len(sys.argv) > 1 else ROOT / "video.mp4"
CHROME = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
VOICE = "vi-VN-NamMinhNeural"
FPS = 30
PAD = 0.55        # silence after each scene's voice
VOICE_DELAY = 0.15

SCENES = [
    dict(mood="sad", html='<div class="big">73 CV</div><div class="hl red">0 CUỘC GỌI 😭</div>',
         voice="Mình đã nộp 73 cái CV... và không một ai gọi lại."),
    dict(mood="tease", html='<div class="mid">Cho đến khi mình<br>sửa đúng <span class="y">1 THỨ</span> 👀</div><div class="chip">XEM HẾT NHÉ!</div>',
         voice="Cho đến khi mình sửa đúng một thứ. Tuần sau đó, có năm công ty gọi mình đi phỏng vấn. Xem hết video, mình chỉ cho bạn thứ đó là gì."),
    dict(mood="sad", html='<div class="mid">Ra trường <span class="y">4 tháng</span>…</div><div class="quote">“Có việc chưa con?” 🥲</div>',
         voice="Ra trường bốn tháng. Bạn bè đi làm hết. Bố mẹ hỏi: có việc chưa con? Mình chỉ dám nói: con đang chờ phản hồi."),
    dict(mood="shock", html='<div class="small">Chị HR 8 năm kinh nghiệm nói:</div><div class="quote big2">“CV em giống<br><span class="y">500</span> cái khác.”</div><div class="chip red">SHOCK 😳</div>',
         voice="Rồi mình gặp một chị làm HR tám năm. Chị nhìn CV của mình đúng sáu giây, rồi nói một câu làm mình đứng hình: CV em giống năm trăm cái khác."),
    dict(mood="tip", html='<div class="chip red">LỖI #1</div><div class="row bad">❌ “Chăm chỉ, nhiệt tình”</div><div class="row good">✅ “Tăng 30% follower<br>fanpage trong 3 tháng”</div>',
         voice="Lỗi thứ nhất. CV mình ghi: chăm chỉ, nhiệt tình, ham học hỏi. Ai cũng ghi y hệt như vậy. Thay vào đó, hãy ghi kết quả bằng con số. Ví dụ: tăng ba mươi phần trăm người theo dõi fanpage trong ba tháng."),
    dict(mood="tip", html='<div class="chip red">LỖI #2 – LỚN NHẤT</div><div class="row bad">❌ 1 CV gửi 73 công ty</div><div class="row good">✅ Sửa CV theo từ khoá<br>của từng tin tuyển dụng</div>',
         voice="Lỗi thứ hai, cũng là lỗi lớn nhất. Mình gửi một bản CV cho cả bảy mươi ba công ty. Chị bảo: em phải lấy đúng từ khoá trong tin tuyển dụng, rồi đưa vào CV."),
    dict(mood="happy", html='<div class="stack"><div>10 CV</div><div class="arrow">⬇</div><div class="y">5 PHỎNG VẤN</div><div class="arrow">⬇</div><div class="g">2 OFFER 🔥</div></div>',
         voice="Mình sửa lại CV, và chỉ nộp đúng mười chỗ phù hợp nhất. Bảy ngày sau: năm lời mời phỏng vấn, và hai offer."),
    dict(mood="happy", html='<div class="chip">TÓM TẮT 3 BƯỚC</div><div class="list"><div><b>1</b> Ghi kết quả bằng số</div><div><b>2</b> Sửa CV theo từng tin</div><div><b>3</b> Nộp ít mà trúng</div></div>',
         voice="Tóm lại ba bước. Một: ghi kết quả bằng con số. Hai: sửa CV theo từng tin tuyển dụng. Ba: nộp ít, mà trúng."),
    dict(mood="happy", html='<div class="hl" style="font-size:96px">LƯU LẠI NGAY📌</div><div class="row good">Comment ngành bạn 👇</div><div class="row">Link tìm việc ở bio 🔗</div>',
         voice="Nếu bạn đang tìm việc, lưu video này lại nhé. Comment ngành của bạn, mình gửi danh sách việc đang tuyển. Link ở bio!"),
]

MOODS = {
    "sad":   ("grayscale(.55) brightness(.75)", "rgba(20,40,90,.35)", "#0b1530"),
    "tease": ("brightness(.85) contrast(1.05)", "rgba(0,0,0,.15)", "#111"),
    "shock": ("contrast(1.15) brightness(.85)", "rgba(160,0,0,.22)", "#2a0505"),
    "tip":   ("brightness(.9)", "rgba(0,0,0,.1)", "#111"),
    "happy": ("saturate(1.25) brightness(1.02)", "rgba(255,170,0,.16)", "#2b1a00"),
}

CSS = """
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:1080px;height:1920px;overflow:hidden;font-family:'Segoe UI Black','Segoe UI',Arial,sans-serif;font-weight:900;color:#fff}
.bg{position:absolute;inset:0;background:%(base)s}
.photo{position:absolute;left:0;top:380px;width:1080px;height:1920px;object-fit:cover;filter:%(filter)s}
.tint{position:absolute;inset:0;background:%(tint)s}
.fade{position:absolute;left:0;right:0;top:0;height:1050px;background:linear-gradient(%(base)s 0%%,%(base)s 32%%,rgba(0,0,0,0) 100%%)}
.text{position:absolute;left:60px;right:60px;top:200px;display:flex;flex-direction:column;align-items:center;gap:28px;text-align:center;
 text-shadow:0 6px 0 #000,0 0 18px rgba(0,0,0,.8);-webkit-text-stroke:3px #000;paint-order:stroke fill}
.big{font-size:230px;line-height:1;color:#ffd400}
.big2{font-size:96px!important}
.hl{font-size:110px;line-height:1.05}
.mid{font-size:92px;line-height:1.15}
.small{font-size:60px}
.quote{font-size:84px;line-height:1.15;font-style:italic}
.y{color:#ffd400}.g{color:#3dff7a}.red{color:#ff3b3b}
.chip{font-size:56px;padding:14px 38px;border-radius:60px;background:#ffd400;color:#000;-webkit-text-stroke:0;text-shadow:none;box-shadow:0 8px 0 #000}
.chip.red{background:#ff2d2d;color:#fff}
.row{font-size:66px;line-height:1.2;padding:22px 34px;border-radius:28px;background:rgba(0,0,0,.55);-webkit-text-stroke:0;text-shadow:0 4px 8px #000;width:100%%}
.row.bad{border:6px solid #ff3b3b;color:#ffb3b3;text-decoration:line-through;text-decoration-thickness:6px}
.row.good{border:6px solid #3dff7a}
.stack{font-size:110px;line-height:1.05;display:flex;flex-direction:column;align-items:center}
.arrow{font-size:70px;-webkit-text-stroke:0}
.list{display:flex;flex-direction:column;gap:22px;width:100%%;text-align:left}
.list div{font-size:68px;padding:20px 30px;background:rgba(0,0,0,.6);border-radius:26px;-webkit-text-stroke:0;text-shadow:0 4px 8px #000}
.list b{display:inline-block;width:86px;height:86px;line-height:86px;text-align:center;border-radius:50%%;background:#ffd400;color:#000;margin-right:18px}
.handle{position:absolute;right:40px;top:130px;font-size:34px;opacity:.8;-webkit-text-stroke:0}
"""


def render_frame(i, scene):
    filt, tint, base = MOODS[scene["mood"]]
    html = f"""<!doctype html><html><head><meta charset="utf-8"><style>{CSS % dict(filter=filt, tint=tint, base=base)}</style></head>
<body><div class="bg"></div><img class="photo" src="{PHOTO.as_uri()}"><div class="tint"></div><div class="fade"></div>
<div class="text">{scene['html']}</div></body></html>"""
    hp = WORK / f"scene{i}.html"
    hp.write_text(html, encoding="utf-8")
    png = WORK / f"scene{i}.png"
    subprocess.run([CHROME, "--headless=new", "--disable-gpu", "--hide-scrollbars", "--force-device-scale-factor=1",
                    "--allow-file-access-from-files", "--virtual-time-budget=1500",
                    "--window-size=1080,1920", f"--screenshot={png}", hp.as_uri()],
                   check=True, capture_output=True)
    return png


async def tts(i, text):
    """The edge-tts service intermittently returns no audio, so retry with backoff."""
    mp3 = WORK / f"voice{i}.mp3"
    for attempt in range(8):
        words = []
        try:
            comm = edge_tts.Communicate(text, VOICE, rate="+6%", boundary="WordBoundary")
            with open(mp3, "wb") as f:
                async for ch in comm.stream():
                    if ch["type"] == "audio":
                        f.write(ch["data"])
                    elif ch["type"] == "WordBoundary":
                        words.append((ch["offset"] / 1e7, (ch["offset"] + ch["duration"]) / 1e7, ch["text"]))
            return mp3, words
        except edge_tts.exceptions.NoAudioReceived:
            await asyncio.sleep(1.5 * (attempt + 1))
    raise RuntimeError(f"TTS failed for scene {i}")


def duration(path):
    r = subprocess.run(["ffprobe", "-v", "error", "-show_entries", "format=duration", "-of", "csv=p=0", str(path)],
                       capture_output=True, text=True, check=True)
    return float(r.stdout.strip())


def ass_time(t):
    h, rem = divmod(t, 3600); m, s = divmod(rem, 60)
    return f"{int(h)}:{int(m):02d}:{s:05.2f}"


def chunk_words(words, text):
    """Group word timings into caption chunks of <=5 words, breaking after punctuation in the source text."""
    chunks, cur = [], []
    pos = 0
    for w in words:
        idx = text.find(w[2], pos)
        end = idx + len(w[2]) if idx >= 0 else pos
        nxt = text[end:end + 1] if idx >= 0 else ""
        pos = end
        cur.append(w)
        if len(cur) >= 6 or nxt in ",.:!?…":
            chunks.append(cur); cur = []
    if cur:
        chunks.append(cur)
    return [(c[0][0], c[-1][1], " ".join(x[2] for x in c)) for c in chunks]


def main():
    voices = asyncio.run(_seq())
    events, clips, t0 = [], [], 0.0
    for i, (scene, (mp3, words)) in enumerate(zip(SCENES, voices)):
        png = render_frame(i, scene)
        dur = round(VOICE_DELAY + duration(mp3) + PAD, 3)
        frames = int(dur * FPS)
        clip = WORK / f"clip{i}.mp4"
        zoom = "min(1+0.0009*on,1.10)" if i % 2 == 0 else "max(1.10-0.0009*on,1.0)"
        vf = (f"scale=2160:3840,zoompan=z='{zoom}':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d={frames}:s=1080x1920:fps={FPS},"
              f"fade=t=in:st=0:d=0.12,format=yuv420p")
        af = (f"[1:a]adelay={int(VOICE_DELAY*1000)}:all=1,apad[v];"
              f"sine=f=1320:d=0.14,afade=t=out:st=0.03:d=0.11,volume=0.18[d];"
              f"[v][d]amix=inputs=2:duration=first:normalize=0,atrim=0:{dur},aresample=44100[a]")
        subprocess.run(["ffmpeg", "-y", "-v", "error", "-loop", "1", "-i", str(png), "-i", str(mp3),
                        "-filter_complex", f"[0:v]{vf}[vv];{af}", "-map", "[vv]", "-map", "[a]",
                        "-t", str(dur), "-r", str(FPS), "-c:v", "libx264", "-preset", "medium", "-crf", "20",
                        "-c:a", "aac", "-b:a", "160k", "-ac", "2", str(clip)], check=True)
        clips.append(clip)
        for s, e, txt in chunk_words(words, scene["voice"]):
            events.append((t0 + VOICE_DELAY + s, t0 + VOICE_DELAY + e + 0.12, txt))
        t0 += duration(clip)
        print(f"scene {i}: {dur:.2f}s", flush=True)

    # make caption chunks contiguous (no flicker gaps < 0.35s)
    for k in range(len(events) - 1):
        if 0 < events[k + 1][0] - events[k][1] < 0.35:
            events[k] = (events[k][0], events[k + 1][0], events[k][2])
    ass = ["[Script Info]", "ScriptType: v4.00+", "PlayResX: 1080", "PlayResY: 1920", "",
           "[V4+ Styles]",
           "Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding",
           "Style: Cap,Arial,74,&H00FFFFFF,&H00FFFFFF,&H00000000,&H96000000,-1,0,0,0,100,100,0,0,1,7,3,2,90,90,430,1",
           "", "[Events]", "Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text"]
    for s, e, txt in events:
        ass.append(f"Dialogue: 0,{ass_time(s)},{ass_time(e)},Cap,,0,0,0,,{{\\fscx85\\fscy85\\t(0,90,\\fscx100\\fscy100)}}{txt.upper()}")
    (WORK / "subs.ass").write_text("\n".join(ass), encoding="utf-8-sig")

    (WORK / "list.txt").write_text("".join(f"file '{c.name}'\n" for c in clips), encoding="utf-8")
    subprocess.run(["ffmpeg", "-y", "-v", "error", "-f", "concat", "-safe", "0", "-i", "list.txt",
                    "-vf", "subtitles=subs.ass", "-c:v", "libx264", "-preset", "medium", "-crf", "19",
                    "-pix_fmt", "yuv420p", "-c:a", "copy", "-movflags", "+faststart", str(OUT.resolve())],
                   check=True, cwd=WORK)
    print("OUT", OUT.resolve(), f"{duration(OUT):.1f}s")


def _seq():
    async def run():
        return [await tts(i, s["voice"]) for i, s in enumerate(SCENES)]
    return run()


if __name__ == "__main__":
    main()
