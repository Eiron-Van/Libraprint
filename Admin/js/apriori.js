// ✅ Advanced Apriori with up to 3-item rules
function apriori(transactions, minSupport = 0.2, minConfidence = 0.5, maxItems = 3) {
  const transactionCount = transactions.length;
  const items = [...new Set(transactions.flat())];
  const rules = [];

  function getSupport(itemset) {
    const count = transactions.filter(t => itemset.every(i => t.includes(i))).length;
    return count / transactionCount;
  }

  function generateCombinations(arr, size) {
    if (size === 1) return arr.map(i => [i]);
    const combos = [];
    arr.forEach((item, i) => {
      const rest = arr.slice(i + 1);
      generateCombinations(rest, size - 1).forEach(next => {
        combos.push([item, ...next]);
      });
    });
    return combos;
  }

  for (let k = 2; k <= maxItems; k++) {
    const combos = generateCombinations(items, k);
    combos.forEach(set => {
      const support = getSupport(set);
      if (support >= minSupport) {
        for (let i = 0; i < set.length - 1; i++) {
          const A = [set[i]];
          const B = set.filter(x => x !== set[i]);
          const conf = getSupport([...A, ...B]) / getSupport(A);
          if (conf >= minConfidence) {
            rules.push({
              rule: `${A.join(', ')} → ${B.join(', ')}`,
              support: support.toFixed(2),
              confidence: conf.toFixed(2)
            });
          }
        }
      }
    });
  }

  return rules;
}
