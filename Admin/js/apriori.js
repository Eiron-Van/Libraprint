document.addEventListener("DOMContentLoaded", () => {
    function apriori(transactions, minSupport = 0.2, minConfidence = 0.5) {
    const itemCounts = {};
    const transactionCount = transactions.length;

    // 1️⃣ Count individual items
    transactions.forEach(t => {
        t.forEach(item => {
        itemCounts[item] = (itemCounts[item] || 0) + 1;
        });
    });

    // 2️⃣ Filter by support
    const items = Object.entries(itemCounts)
        .filter(([_, count]) => count / transactionCount >= minSupport)
        .map(([item]) => item);

    // 3️⃣ Find pairs (simple 2-item rules for readability)
    const rules = [];
    for (let i = 0; i < items.length; i++) {
        for (let j = i + 1; j < items.length; j++) {
        const [A, B] = [items[i], items[j]];
        let countAB = 0, countA = 0;

        transactions.forEach(t => {
            if (t.includes(A)) countA++;
            if (t.includes(A) && t.includes(B)) countAB++;
        });

        const support = countAB / transactionCount;
        const confidence = countAB / countA;

        if (support >= minSupport && confidence >= minConfidence) {
            rules.push({
            rule: `${A} → ${B}`,
            support: support.toFixed(2),
            confidence: confidence.toFixed(2)
            });
        }
        }
    }

    return rules;
    }

});