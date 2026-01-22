import sys
import json
import re
import numpy as np
from collections import Counter
import math

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
}

def expand_query(query_tokens):
    expanded = set(query_tokens)
    for token in query_tokens:
        if token in SYNONYMS:
            expanded.update(SYNONYMS[token])
        # Reverse lookup (if user uses a synonym, map back to key?)
        # Maybe overkill, just one-way expansion is usually enough for "intent" -> "content"
    return list(expanded)

def normalize_text(text):
    if not text:
        return ""
    # Lowercase
    text = text.lower()
    # Remove punctuation
    text = re.sub(r'[^\w\s]', ' ', text)
    # Remove extra spaces
    text = re.sub(r'\s+', ' ', text).strip()
    return text

def tokenize(text):
    tokens = text.split()
    return [t for t in tokens if t not in STOPWORDS]

class TFIDFSearch:
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
            # Combine title and description, give more weight to title
            title_text = normalize_text(doc.get('title', ''))
            desc_text = normalize_text(doc.get('description', ''))
            tags_text = normalize_text(doc.get('tags', ''))
            cat_text = normalize_text(doc.get('categories', ''))
            
            # Simple weighting: repeat title tokens, include tags and categories
            text = (title_text + " ") * 3 + desc_text + " " + (tags_text + " ") * 2 + cat_text
            tokens = tokenize(text)
            processed_docs.append(tokens)
            
            unique_tokens = set(tokens)
            for token in unique_tokens:
                doc_freqs[token] += 1
                
        # 2. Compute IDF
        self.vocab = {term: idx for idx, term in enumerate(doc_freqs.keys())}
        vocab_size = len(self.vocab)
        
        for term, freq in doc_freqs.items():
            self.idf[term] = math.log(1 + num_docs / (1 + freq)) + 1 # Smooth IDF
            
        # 3. Compute TF-IDF Vectors for Documents
        self.doc_vectors = np.zeros((num_docs, vocab_size))
        
        for i, tokens in enumerate(processed_docs):
            term_counts = Counter(tokens)
            total_terms = len(tokens) if len(tokens) > 0 else 1
            
            for term, count in term_counts.items():
                if term in self.vocab:
                    idx = self.vocab[term]
                    tf = count / total_terms
                    self.doc_vectors[i, idx] = tf * self.idf[term]
                    
        # Normalize vectors
        norms = np.linalg.norm(self.doc_vectors, axis=1, keepdims=True)
        norms[norms == 0] = 1 # Avoid division by zero
        self.doc_vectors = self.doc_vectors / norms

    def search(self, query):
        query_norm = normalize_text(query)
        query_tokens = tokenize(query_norm)
        
        # Expand query with synonyms (Intent)
        query_tokens = expand_query(query_tokens)
        
        if not query_tokens:
            return []
            
        vocab_size = len(self.vocab)
        query_vec = np.zeros(vocab_size)
        
        term_counts = Counter(query_tokens)
        total_terms = len(query_tokens)
        
        for term, count in term_counts.items():
            if term in self.vocab:
                idx = self.vocab[term]
                tf = count / total_terms
                query_vec[idx] = tf * self.idf[term]
                
        # Normalize query vector
        query_norm_val = np.linalg.norm(query_vec)
        if query_norm_val == 0:
            return []
        query_vec = query_vec / query_norm_val
        
        # Cosine Similarity
        # (num_docs, vocab) . (vocab,) -> (num_docs,)
        scores = np.dot(self.doc_vectors, query_vec)
        
        results = []
        for i, score in enumerate(scores):
            if score > 0.05: # Threshold
                doc = self.documents[i]
                doc['relevance_score'] = float(score) # Convert numpy float to python float
                results.append(doc)
                
        # Sort by relevance
        results.sort(key=lambda x: x['relevance_score'], reverse=True)
        return results

if __name__ == "__main__":
    try:
        if len(sys.argv) < 3:
            print(json.dumps({"error": "Insufficient arguments"}))
            sys.exit(1)
            
        documents_file_path = sys.argv[1]
        query = sys.argv[2]
        
        with open(documents_file_path, 'r', encoding='utf-8') as f:
            documents = json.load(f)
            
        if not documents:
            print(json.dumps([]))
            sys.exit(0)
            
        engine = TFIDFSearch(documents)
        results = engine.search(query)
        
        print(json.dumps(results))
        
    except Exception as e:
        # Fallback or error reporting
        # print(json.dumps({"error": str(e)}))
        # For production, maybe return empty list to avoid breaking UI
        print(json.dumps([]))
        sys.exit(1)
