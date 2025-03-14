// src/components/ProductList.jsx
import React from 'react';
import { Grid } from '@mui/material';
import ProductCard from './ProductCard';

// Sample static product data
const products = [
  { id: 1, name: 'Soccer Ball',    price: '$29.99',  description: 'Standard size 5 soccer ball for training and matches.' },
  { id: 2, name: 'Running Shoes',  price: '$79.99',  description: 'Lightweight running shoes with breathable mesh.' },
  { id: 3, name: 'Basketball Hoop',price: '$199.99', description: 'Outdoor basketball hoop with adjustable height.' }
];

const ProductList = () => {
  return (
    <Grid container spacing={2} sx={{ p: 2 }}>
      {products.map(product => (
        <Grid item xs={12} sm={6} md={4} key={product.id}>
          <ProductCard product={product} />
        </Grid>
      ))}
    </Grid>
  );
};

export default ProductList;
