(()=>{const t=document.querySelectorAll(".mzb-tab-post");if(t?.length)for(const e of t){const t=e&&e.querySelectorAll(".mzb-tab-title");if(!t?.length)return;for(const r of t)r.addEventListener("click",(t=>{const a=(o=r,null===o.parentNode?[]:Array.prototype.filter.call(o.parentNode.children,(function(t){return t!==o})))?.[0];var o;e.setAttribute("data-active-tab",t.target.getAttribute("data-tab")||""),r.classList.add("active"),a?.classList?.remove("active")}))}})();