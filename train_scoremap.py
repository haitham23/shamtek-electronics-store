import os
import json
import csv
from math import log
from collections import defaultdict, Counter

# -----------------------------
# Tokenizer (lowercase + alnum + ')
# -----------------------------
def tokenize(text: str):
    text = text.lower()
    tokens = []
    cur = []

    for ch in text:
        if ch.isalnum() or ch == "'":
            cur.append(ch)
        else:
            if cur:
                tok = "".join(cur).strip("'")
                if tok:
                    tokens.append(tok)
                cur = []

    if cur:
        tok = "".join(cur).strip("'")
        if tok:
            tokens.append(tok)

    return tokens


# -----------------------------
# 1) labels من CSV
# -----------------------------
labels = {}
with open("annotations_metadata.csv", "r", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    for row in reader:
        labels[row["file_id"] + ".txt"] = row["label"]


# -----------------------------
# 2) تحميل النصوص
# -----------------------------
texts = []
label_list = []

folder = "sampled_train"
for filename in os.listdir(folder):
    if filename.endswith(".txt"):
        with open(os.path.join(folder, filename), "r", encoding="utf-8") as f:
            texts.append(f.read())
            label_list.append(labels.get(filename, "noHate"))

N = len(texts)
if N == 0:
    raise RuntimeError("sampled_train فارغ: ما كاين حتى ملف .txt")


# -----------------------------
# 3) حساب df و idf
# -----------------------------
idf_df = defaultdict(int)

for t in texts:
    for w in set(tokenize(t)):
        idf_df[w] += 1

idf = {}
for w, df in idf_df.items():
    idf[w] = log(N / (1 + df))


# -----------------------------
# 4) ScoreMap (TF-IDF) + فلتر DF
# -----------------------------
ScoreMap = defaultdict(float)

MAX_DF_RATIO = 0.05
# أي كلمة كاينة فـ أكثر من 5% ديال الوثائق => نحيدوها (stopwords)
# إذا بقات "are" كاينة، نقصيها لـ 0.03 أو 0.02

for text, label in zip(texts, label_list):
    words = tokenize(text)
    if not words:
        continue

    counts = Counter(words)
    total = len(words)

    for w, c in counts.items():
        df_ratio = idf_df.get(w, 0) / N
        if df_ratio > MAX_DF_RATIO:
            continue

        tf = c / total
        tfidf = tf * idf.get(w, 0.0)

        if label == "hate":
            ScoreMap[w] -= tfidf
        else:
            ScoreMap[w] += tfidf


# -----------------------------
# 5) حفظ
# -----------------------------
with open("scoremap.json", "w", encoding="utf-8") as f:
    json.dump(ScoreMap, f, indent=2, ensure_ascii=False)

print("✔ scoremap.json généré avec succès !")
print("✔ N (documents) =", N)
print("✔ MAX_DF_RATIO =", MAX_DF_RATIO)
print("✔ Nombre de mots dans ScoreMap:", len(ScoreMap))
