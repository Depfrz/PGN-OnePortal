import sys
import json
import re
import math
from collections import Counter

# Simple Stopwords (Indonesian & English common)
STOPWORDS = set([
    'dan', 'atau', 'yang', 'untuk', 'di', 'ke', 'dari', 'ini', 'itu', 'dengan', 
    'adalah', 'pada', 'juga', 'saya', 'anda', 'kami', 'kita', 'bisa', 'dapat',
    'the', 'and', 'or', 'of', 'to', 'in', 'for', 'with', 'on', 'at', 'is', 'a', 'an'
])

# Synonym Mapping for Intent
SYNONYMS = {
    'cara': ['panduan', 'pedoman', 'guide', 'tutorial', 'langkah', 'instruksi'],
    'hapus': ['delete', 'remove', 'buang', 'hilangkan', 'bersihkan'],
    'tambah': ['add', 'create', 'buat', 'new', 'baru', 'insert'],
    'edit': ['ubah', 'ganti', 'update', 'modify', 'change'],
    'cari': ['search', 'find', 'temukan', 'lihat', 'view'],
    'laporan': ['report', 'rekap', 'summary', 'data'],
    'aturan': ['rule', 'policy', 'kebijakan', 'regulari'],
    'periksa': ['cek', 'inspeksi', 'audit', 'monitoring', 'pemeriksaan'],
    'cek': ['periksa', 'inspeksi', 'audit', 'monitoring', 'pemeriksaan'],
    'inspeksi': ['periksa', 'cek', 'audit', 'monitoring', 'pemeriksaan'],
    'pemeriksaan': ['cek', 'inspeksi', 'audit', 'monitoring', 'periksa'],
    'panduan': ['cara', 'pedoman', 'guide', 'tutorial', 'langkah', 'instruksi'],
    'instruksi': ['cara', 'pedoman', 'guide', 'tutorial', 'langkah', 'panduan'],
    'kerja': ['work', 'job', 'pekerjaan', 'tugas'],
}

def expand_query(query_tokens):
    expanded = set(query_tokens)
    for token in query_tokens:
        if token in SYNONYMS:
            expanded.update(SYNONYMS[token])
    return list(expanded)

def normalize_text(text):
    if not text:
        return ""
    text = str(text).lower()
    text = re.sub(r'[^\w\s]', ' ', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text

def tokenize(text):
    tokens = text.split()
    return [t for t in tokens if t not in STOPWORDS]

class SimpleTFIDFSearch:
    def __init__(self, documents):
        self.documents = documents
        self.vocab = {}
        self.idf = {}
        self.doc_vectors = []
        self.build_model()

    def build_model(self):
        # 1. Build Vocabulary and Doc Frequencies
        doc_freqs = Counter()
        num_docs = len(self.documents)
        
        processed_docs = []
        
        for doc in self.documents:
            title_text = normalize_text(doc.get('title', ''))
            desc_text = normalize_text(doc.get('description', ''))
            tags_text = normalize_text(doc.get('tags', ''))
            # cat_text = normalize_text(doc.get('categories', '')) # Category might not exist in all docs
            
            # Weighted concatenation
            text = (title_text + " ") * 3 + desc_text + " " + (tags_text + " ") * 2
            tokens = tokenize(text)
            processed_docs.append(tokens)
            
            unique_tokens = set(tokens)
            for token in unique_tokens:
                doc_freqs[token] += 1
                
        # 2. Compute IDF
        self.vocab = {term: idx for idx, term in enumerate(doc_freqs.keys())}
        vocab_size = len(self.vocab)
        
        for term, freq in doc_freqs.items():
            self.idf[term] = math.log(1 + num_docs / (1 + freq)) + 1
            
        # 3. Compute TF-IDF Vectors
        self.doc_vectors = []
        
        for tokens in processed_docs:
            vec = [0.0] * vocab_size
            term_counts = Counter(tokens)
            total_terms = len(tokens) if len(tokens) > 0 else 1
            
            for term, count in term_counts.items():
                if term in self.vocab:
                    idx = self.vocab[term]
                    tf = count / total_terms
                    vec[idx] = tf * self.idf[term]
            
            # Normalize vector
            norm = math.sqrt(sum(x*x for x in vec))
            if norm > 0:
                vec = [x/norm for x in vec]
            
            self.doc_vectors.append(vec)

    def search(self, query):
        query_norm = normalize_text(query)
        query_tokens = tokenize(query_norm)
        query_tokens = expand_query(query_tokens)
        
        if not query_tokens:
            return []
            
        vocab_size = len(self.vocab)
        query_vec = [0.0] * vocab_size
        
        term_counts = Counter(query_tokens)
        total_terms = len(query_tokens)
        
        for term, count in term_counts.items():
            if term in self.vocab:
                idx = self.vocab[term]
                tf = count / total_terms
                query_vec[idx] = tf * self.idf[term]
                
        # Normalize query vector
        query_norm_val = math.sqrt(sum(x*x for x in query_vec))
        if query_norm_val == 0:
            return []
        query_vec = [x/query_norm_val for x in query_vec]
        
        # Cosine Similarity
        results = []
        for i, doc_vec in enumerate(self.doc_vectors):
            # Dot product
            score = sum(doc_vec[j] * query_vec[j] for j in range(vocab_size))
            
            # Lower threshold even more for broad matching
            # Since we have fallback separation in UI, it's better to show somewhat relevant results
            # than nothing.
            if score > 0.001: 
                doc = self.documents[i]
                doc['relevance_score'] = score
                results.append(doc)
                
        results.sort(key=lambda x: x['relevance_score'], reverse=True)
        return results

if __name__ == "__main__":
    try:
        # Expected args: script.py [json_file_path] [query_string]
        if len(sys.argv) < 3:
            # Fallback if arguments missing, just print empty array
            print(json.dumps([]))
            sys.exit(0)
            
        documents_file_path = sys.argv[1]
        query = sys.argv[2]
        
        with open(documents_file_path, 'r', encoding='utf-8') as f:
            documents = json.load(f)
            
        if not documents:
            print(json.dumps([]))
            sys.exit(0)
            
        engine = SimpleTFIDFSearch(documents)
        results = engine.search(query)
        
        print(json.dumps(results))
        
    except Exception as e:
        # In case of any error, print empty list so PHP doesn't crash on json_decode
        # print(str(e), file=sys.stderr) # Debug info to stderr
        print(json.dumps([]))
        sys.exit(0)
