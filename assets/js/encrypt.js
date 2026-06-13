async function encrypt(text, key) {
	const iv = crypto.getRandomValues(new Uint8Array(12));
	const encoded = new TextEncoder().encode(text);

	const encrypted = await crypto.subtle.encrypt(
		{ name: "AES-GCM", iv },
		key,
		encoded
	);

	return { iv, encrypted };
}

const { iv, encrypted } = await encrypt("Hola mundo", key);

await fetch("/api/mensaje", {
	method: "POST",
	headers: { "Content-Type": "application/json" },
	body: JSON.stringify({
		iv: Array.from(iv),
		data: Array.from(new Uint8Array(encrypted))
	})
});

async function decrypt(payload, key) {
	const iv = new Uint8Array(payload.iv);
	const data = new Uint8Array(payload.data);

	const decrypted = await crypto.subtle.decrypt(
		{ name: "AES-GCM", iv },
		key,
		data
	);

	return new TextDecoder().decode(decrypted);
}
